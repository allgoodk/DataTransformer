<?php


namespace Gtt\Bundle\DataTransformerBundle\Tests;


use Gtt\Bundle\DataTransformerBundle\Service\AbstractFieldSetBuilder;
use Gtt\Bundle\DataTransformerBundle\Tests\TestFieldSetBuilder\TestFieldSetBuilder;
use RuntimeException;

class FieldSetTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string
     */
    private $testData;

    /**
     * @var AbstractFieldSetBuilder
     */
    private $fieldSetBuilder;

    /**
     * @var array
     */
    private $returnedData;

    /**
     * @var array
     */
    private $testFailedData;

    protected function setUp()
    {
        $this->testData = [
            'card_num'   => '123421xxxxxx1234',
            'mrch'       => 'sandbox',
            'SIGN'       => '17037cca0ff480ebac2b9fae6efae201',
            'SUCCESS'    => 'TRUE',
            'CARD_INFO'  => [
                'TestPS' => [
                    'holder_first_name'  => 'Dmitry',
                    'holder_second_name' => 'Gorbachev',
                    'cvv'                => 123,
                ],
            ],
            'OTHER_INFO' => [
                'TRANSAC_ID' => 12789,
            ],
        ];

        $this->testFailedData = [
            'mrch'       => 'sandbox',
            'SIGN'       => '17037cca0ff480ebac2b9fae6efae201',
            'SUCCESS'    => 'UNSUCCESS',
            'CARD_INFO'  => [
                'TestPS' => [
                    'holder_first_name'  => 'Dmitry',
                    'holder_second_name' => 'Gorbachev',
                    'cvv'                => 123,
                ],
            ],
            'OTHER_INFO' => [
                'TRANSAC_ID' => 12789,
            ],
        ];


        $this->fieldSetBuilder = new TestFieldSetBuilder();
        $this->returnedData    = $this->fieldSetBuilder
            ->build(
                [
                    'merchant_alias' => 'sandbox',
                    'get_signature'  => function ($data) {
                        $transactionId = $data['OTHER_INFO']['TRANSAC_ID'];
                        $cardNum       = $data['card_num'];

                        return md5($cardNum . $transactionId);
                    },
                ]
            )
            ->process($this->testData)->getData();
    }

    public function testFieldWithDataFromAnotherFieldSet()
    {
        $this->assertArrayHasKey('transaction_id', $this->returnedData);
        $this->assertEquals('12789', $this->returnedData['transaction_id']);
    }

    public function testFieldWithDataFromSeverealFieldsOfAnotherFieldSetWithTransformer()
    {
        $this->assertArrayHasKey('client_name', $this->returnedData);
        $this->assertEquals('*mitry Gorbachev', $this->returnedData['client_name']);
    }

    public function testFieldWithDataValidatedWithDataFromOptions()
    {
        $this->assertArrayHasKey('merchant_alias', $this->returnedData);
        $this->assertEquals('sandbox', $this->returnedData['merchant_alias']);
    }

    public function testFieldWithDataCreatedByCallbackFromOptions()
    {
        $this->assertArrayHasKey('signature', $this->returnedData);
        $this->assertEquals('17037cca0ff480ebac2b9fae6efae201', $this->returnedData['signature']);
    }

    public function testFieldWithDataFromAnotherFieldSetValidatedAndTransformed()
    {
        $this->assertArrayHasKey('internal_transaction_status', $this->returnedData);
        $this->assertArrayHasKey('transaction_status', $this->returnedData);
        $this->assertEquals('TRUE', $this->returnedData['internal_transaction_status']);
        $this->assertEquals('success', $this->returnedData['transaction_status']);
    }

    public function testOptionsExpectations()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('merchant_alias:  This value should be equal to null.');
        (new TestFieldSetBuilder())->build()->process($this->testData)->getData();
    }

    public function testCustomFieldSource()
    {
        $this->assertArrayHasKey('custom_field_source', $this->returnedData);
        $this->assertEquals(['Dmitry' => 'test2'], $this->returnedData['custom_field_source']);
    }

    public function testDependentFieldConstraints()
    {
        $data = (new TestFieldSetBuilder())
            ->build(
                [
                    'merchant_alias' => 'sandbox',
                ]
            )
            ->process($this->testFailedData)->getData();

        $this->assertArrayHasKey('client_card_number', $data);
        $this->assertEquals('', $data['client_card_number']);
        $this->assertArrayHasKey('internal_transaction_status', $data);
        $this->assertEquals('UNSUCCESS', $data['internal_transaction_status']);
    }
}