<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Client\Model\Request;

class ShopperCreate
{
    /**
     * Gender constants
     */
    public const GENDER_MALE = 'M';
    public const GENDER_FEMALE = 'F';
    public const GENDER_UNKNOWN = 'U';

    /**
     * Genders Mapping
     */
    public const GENDERS_MAPPING = [
        '1' => self::GENDER_MALE,
        '2' => self::GENDER_FEMALE,
        '3' => self::GENDER_UNKNOWN
    ];

    /**
     * @var ?string
     */
    private $shopperId;

    /**
     * @var ?array
     */
    private $name;

    /**
     * @var ?array
     */
    private $address;

    /**
     * @var ?string
     */
    private $email;

    /**
     * @var ?string
     */
    private $gender;

    /**
     * @var ?string
     */
    private $dateOfBirth;

    /**
     * @var ?string
     */
    private $phoneNumber;

    /**
     * ShopperCreate constructor
     *
     * @param ?string $shopperId
     * @param ?array $name
     * @param ?array $address
     * @param ?string $email
     * @param ?string $gender
     * @param ?string $dateOfBirth
     * @param ?string $phoneNumber
     */
    public function __construct(
        ?string $shopperId = null,
        ?array $name = null,
        ?array $address = null,
        ?string $email = null,
        ?string $gender = null,
        ?string $dateOfBirth = null,
        ?string $phoneNumber = null
    ) {
        $this->shopperId = $shopperId;
        $this->name = $name;
        $this->address = $address;
        $this->email = $email;
        $this->gender = $gender;
        $this->dateOfBirth = $dateOfBirth;
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * Convert object to array
     *
     * @return array
     */
    public function toArray(): array
    {
        return array_filter([
            'shopper_reference' => $this->shopperId,
            'name' => $this->name,
            'address' => $this->address,
            'email' => $this->email,
            'gender' => $this->gender,
            'date_of_birth' => $this->dateOfBirth,
            'phone_number' => $this->phoneNumber
        ]);
    }

    /**
     * @return ?string
     */
    public function getShopperId(): ?string
    {
        return $this->shopperId;
    }

    /**
     * @param string $shopperId
     */
    public function setShopperId(string $shopperId): void
    {
        $this->shopperId = $shopperId;
    }

    /**
     * @return ?array
     */
    public function getName(): ?array
    {
        return $this->name;
    }

    /**
     * @param array $name
     */
    public function setName(array $name): void
    {
        $this->name = $name;
    }

    /**
     * @return ?array
     */
    public function getAddress(): ?array
    {
        return $this->address;
    }

    /**
     * @param array $address
     */
    public function setAddress(array $address): void
    {
        $this->address = $address;
    }

    /**
     * @return string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return ?string
     */
    public function getGender(): ?string
    {
        return $this->gender;
    }

    /**
     * @param ?string $gender
     */
    public function setGender(?string $gender): void
    {
        $this->gender = $gender;
    }

    /**
     * @return ?string
     */
    public function getDateOfBirth(): ?string
    {
        return $this->dateOfBirth;
    }

    /**
     * @param ?string $dateOfBirth
     */
    public function setDateOfBirth(?string $dateOfBirth): void
    {
        $this->dateOfBirth = $dateOfBirth;
    }

    /**
     * @return string
     */
    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    /**
     * @param ?string $phoneNumber
     */
    public function setPhoneNumber(?string $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }
}
