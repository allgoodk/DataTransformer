# Send-receiver Service

* go run -race
  > флаг для отладки блокировок
  
***

[https://echo.labstack.com/guide](https://echo.labstack.com/guide)




##### FieldSet builder - альтернатива symfony form, для трансформации и валидации данных от платежных систем (ПС) и наоборот.

### Создание филдсета для новой ПС

Создаем свой филдсет, наследующийся от AbstractFieldsetBuilder:

```
class RietumuRedirectDataFieldSetBuilder extends AbstractFieldSetBuilder
```

при необходимости инжектим в него нормалайзер(про нормалайзеры ссылка) и вызываем метод setNormalizer():

```
public function __construct(NormalizerInterface $normalizer)
{
     parent::__construct();
     
     $this->setNormalizer(
          $normalizer,
          [
               TransactionRedirectRequestDtoNormalizer::CONTEXT_CALLBACK_DEPOSIT_URL, // Здесь передается контекст для выполнения
               TransactionRedirectRequestDtoNormalizer::CONTEXT_CALLBACK_REDIRECT_URL,
          ]
     );
}
```

Реализуем метод build($options = []) в котором собираем поля(здесь ссылка на сборку полей). 
$options - дополнительные опции, которые мы можем пробросить из вне. Настройки общие и для FieldSet и для Normalizer:

```
public function build(array $options = []): FieldSet
{
     $this->configureFieldSetOptions($options); // Выставляем настройки из вне (например merchantAlias, signature и тд и тп)
 
     $this->addField(
          'merchant_transaction_id', 
          new FieldSource(TransactionDetailEnum::REQUEST_ID)
     ); 
     // Первый аргумент имя поля, второй аргумент источник данных, третий аргумент - дефолтные значения для поля, если в источнике нет данных
```

Источник может ссылаться на одно (в примере выше) или несколько полей(пример ниже) во входных данных:

```
$this->addField(
    TransactionDetailEnum::TRANSACTION_ID, 
    new FieldSource(['OTHER_INFO', 'TRANSAC_ID'])
);
```

Так же, источником данных может быть другой FieldSet, в таком случае необходимо вторым аргументом передать класс FieildSet'a, откуда мы берем данные: 

```
$this->addField(
    TransactionDetailEnum::TRANSACTION_ID,
    new FieldSource([TransactionDetailEnum::TRANSACTION_ID], PaytureOtherInfoFieldSetBuilder::class) // Здесь вторым аргументом передается класс FieldSeta источника
);
```

### Валидация полей

При необходимости на каждое поле можно навешивать симфонийские констреинты

```
// простейший пример, передаем констрейнт NotBlank
->addFieldValidators([new FieldConstraint(NotBlank::class)]); 
```
В случае, если для констреинта нужно передать параметры (например для EqualTo значение, с которым сравниваем значение в поле), то вторым аргументом в FieldConstraint передаем имя опции, которые мы пробросили.

В методе build нашего fieldSetBuilder'а:

```
$this
    ->addField('merchant_alias', new FieldSource('mrch'))
    ->addFieldValidators(
        [new FieldConstraint(EqualTo::class, 'merchant_alias'),] 
        // В метод build был передан массив опций $options = ['merchant_alias' => 'merchantNamePyshPysh']
    );
```

Опцией может быть анонимная функция, которая выполнится при необходимости получения данных:

```
$form = $this->fieldSetBuilder
    ->build(
        [
            'merchant_alias' => 'sandbox',
            'get_signature'  => function ($data) { // Дальше мы можем получить доступ к этим данным в валидаторе, который сам выполнит эту функцию и вернет результат для валидации
                $transactionId = $data['OTHER_INFO']['TRANSAC_ID'];
                $cardNum       = $data['card_num'];
 
                return md5($cardNum . $transactionId);
            },
        ]
    )
 
// в самом валидаторе достаточно просто указать имя нашей опции
$this->addField('signature', new FieldSource('SIGN'))
     ->addFieldValidators([new FieldConstraint(EqualTo::class, 'get_signature')]); // <- Вот об этом я :)
```


### Трансформеры на поля

На каждое поле можно навесить массив стандартных симфонийских трансформеров (классы имплементирующие DataTransformerInterface).

```
$this->addField(TransactionDetailEnum::TRANSACTION_STATUS, new FieldSource('SUCCESS'))
     ->addFieldTransformer([new StatusMapperTransformer(['TRUE'])]); // Здесь в конструктор передается статус, который будет признаком успешного ответа от ПС. ПЕРЕДАЕМ МАССИВ ТРАНСФОРМЕРОВ!
```

Для добавления своего трансформера, достаточно имплемениторовать интерфейс DataTransformerInterface

```
class CardMaskToAsteriskTransformer implements DataTransformerInterface
{
    /**
     * Given symbol will replaced to '*' symbol. For example, 'x' -> '*'
     *
     * @var string
     */
    private $sourceSymbolToReplace;
 
    /**
     * PaytureCardMaskToAsteriskTransformer constructor.
     *
     * @param string $sourceSymbolToReplace Given symbol will replaced to '*' symbol. For example, 'x' -> '*'
     */
    public function __construct($sourceSymbolToReplace = 'x')
    {
        $this->sourceSymbolToReplace = $sourceSymbolToReplace;
    }
 
    /** {@inheritdoc} */
    public function transform($transformedValue)
    {
        return $transformedValue === null ? null : str_replace('*', $this->sourceSymbolToReplace, $transformedValue);
    }
 
    /** {@inheritdoc} */
    public function reverseTransform($valueToTransform)
    {
        return $valueToTransform === null ? null : str_replace($this->sourceSymbolToReplace, '*', $valueToTransform);
    }
}
```

### Нормалайзеры

Нормалайзеры в FieldSet используются для нормализации данных приходящих от внешних источников данных. 

Базовый абстрактный класс AbstractFieldSetNormalizer имплементирует symfony NormalizerInterface.

У всех новых нормалайзеров должны быть реализованы методы normalize($data, $format = null, $context = []), где $data - данные для нормализации, $format - выходной формат данных (не используется) и $context - контекст нормализации.

В примере рассмотрен метод normalize класса TransactionRedirectRequestDtoNormalizer, который из TransactionRedirectRequestDto делает ассоциативный массив

```
public function normalize($object, $format = null, array $context = []) // контекст устанавливает какие урлы мы можем вернуть из returnDataService
{
     $propertyList = [];
         foreach ($object->transferDetailList as $propertyDto) {
         $propertyList[$propertyDto->name] = $propertyDto->value;
     }
 
      if (\in_array(self::CONTEXT_CALLBACK_DEPOSIT_URL, $context, true)) {
          $propertyList[TechnicalDetailEnum::CALLBACK_URL] = $this->returnDataService->getHandleCallbackUrl(
          $this->platformType,
          $this->merchantAlias,
          CallbackTypeEnum::DEPOSIT
        );
 }
 
     if (\in_array(self::CONTEXT_CALLBACK_REDIRECT_URL, $context, true)) {
         $propertyList[TechnicalDetailEnum::RETURN_URL] = $this->returnDataService->getHandleCallbackUrl(
         $this->platformType,
         $this->merchantAlias,
         CallbackTypeEnum::REDIRECT,
         [ 
            'transfer_id' => $propertyList[TransactionDetailEnum::REQUEST_ID],
            'language' => $propertyList[TechnicalDetailEnum::CLIENT_SITE_LANGUAGE],
         ]
       );
   }
 
    return $propertyList;
}
```
