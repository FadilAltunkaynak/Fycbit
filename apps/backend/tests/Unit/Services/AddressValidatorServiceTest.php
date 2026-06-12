<?php

namespace Tests\Unit\Services;
use App\Http\Services\Solana\AddressValidator;
use PHPUnit\Framework\TestCase;
use StephenHill\Base58;

class AddressValidatorServiceTest extends TestCase
{
    private Base58 $mockBase58;
    private AddressValidator $addressValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockBase58 = $this->createMock(Base58::class);
        $this->addressValidator = new AddressValidator($this->mockBase58);
    }

    public function testValidateAddressReturnsTrueForValidAddress(): void
    {
        $validDecodedAddress = str_repeat('a', 32);

        $this->mockBase58->method('decode')
            ->willReturn($validDecodedAddress);

        $result = $this->addressValidator->validateAddress('validBase58Address');

        $this->assertTrue($result, 'Expected validateAddress to return true for a valid address.');
    }

    public function testValidateAddressReturnsFalseForInvalidAddress(): void
    {
        $this->mockBase58
            ->method('decode')
            ->willThrowException(new \Exception('Invalid address'));

        $result = $this->addressValidator->validateAddress('invalidBase58Address');

        $this->assertFalse($result, 'Expected validateAddress to return false for an invalid address.');
    }

    public function testValidateAddressReturnsFalseForNon32ByteDecodedAddress(): void
    {
        $invalidDecodedAddress = str_repeat('a', 31);

        // Configure the mock to return an invalid decoded address
        $this->mockBase58
            ->method('decode')
            ->willReturn($invalidDecodedAddress);

        $result = $this->addressValidator->validateAddress('shortDecodedAddress');

        $this->assertFalse($result, 'Expected validateAddress to return false for a decoded address not 32 bytes long.');
    }
}
