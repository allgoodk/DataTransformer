<?php


namespace  Gtt\Bundle\DataTransformerBundle\Transformer;


use Gtt\Bundle\CatsClient\Enum\Currency as CurrencyEnum;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class CryptoCurrencyToAmountPowerTransformer
 */
class CryptoCurrencyToAmountPowerTransformer implements DataTransformerInterface
{

    /**
     * Crypto currency power to get micro coins (for example 1 BTC  = 1 * 10^power, where power = 8)
     */
    const CRYPTO_CURRENCY_POWER = [
        CurrencyEnum::BTC_ID => 8,
        CurrencyEnum::LTC_ID => 8,
        CurrencyEnum::ETH_ID => 18,
    ];

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        $currencyCode = is_array($value) ? current($value) : $value;

        return array_key_exists($currencyCode, self::CRYPTO_CURRENCY_POWER)
            ? self::CRYPTO_CURRENCY_POWER[$currencyCode]
            : 1; // If crypto has no microcoins, return 1 (for example, in 1 satoshi - 1 satoshi)
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        return $value;
    }
}