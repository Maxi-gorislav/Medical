<?php
/**
 * Carts Guru
 *
 * @author    LINKT IT
 * @copyright Copyright (c) LINKT IT 2016
 * @license   Commercial license
 */

class CartsGuruAccountMapper extends CartsGuruMapperAbstract
{
    /**
     * (non-PHPdoc)
     * @see CartsGuruMapperAbstract::mappObject()
     */
    public function mappObject($customer, $params)
    {
        $gender = $this->getGender($customer);
        $lastname = $customer->lastname;
        $firstname = $customer->firstname;
        $homePhoneNumber = '';
        $mobilePhoneNumber = '';
        $country_iso_code = '';
        $address = $this->getLastAddress($customer->id);
        if ($address) {
            $homePhoneNumber = $address['phone'];
            $mobilePhoneNumber = $address['phone_mobile'];
            $country_iso_code = $address['country_iso_code'];
        }
        return array(
            'accountId' => $this->notEmpty($customer->email), // Account id of the customer
            'civility' => $gender, // Use string in this list : 'mister','madam'
            'lastname' => $this->notEmpty($lastname), // Lastname of the buyer
            'firstname' => $this->notEmpty($firstname), // Firstname of the buyer
            'email' => $this->notEmpty($customer->email), // Email of the customer
            'homePhoneNumber' => $homePhoneNumber,
            'mobilePhoneNumber' => $mobilePhoneNumber,
            'countryCode' => $this->notEmpty($country_iso_code)
        );
    }

    /**
     * get customer gender and format it
     *
     * @param $customer
     * @return string
     */
    public function getGender($customer)
    {
        $gender_name = 'mister';
        if (version_compare(_PS_VERSION_, '1.5.0', '>=') && (int) $customer->id_gender) {
            $gender = new Gender((int) $customer->id_gender, $this->id_lang);
            if ((int) $gender->type == 2) {
                $gender_name = 'madam';
            }
        }
        if (version_compare(_PS_VERSION_, '1.5.0', '<') && (int) $customer->id_gender) {
            if ((int) $customer->id_gender == 2) {
                $gender_name = 'madam';
            }
        }
        return ($gender_name);
    }

    /**
     * Get the last address saved
     */
    public function getLastAddress($id_customer)
    {
        $last_address = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
			SELECT a.`phone`, a.`phone_mobile`, cl.`name` as country_name, c.`iso_code` as country_iso_code
			FROM `' . _DB_PREFIX_ . 'address` a
			LEFT JOIN `' . _DB_PREFIX_ . 'country` c ON c.`id_country` = a.`id_country`
			LEFT JOIN `' . _DB_PREFIX_ . 'country_lang` cl ON c.`id_country` = cl.`id_country`
				AND cl.`id_lang` = ' . (int) $this->id_lang . '
			WHERE a.id_customer=' . (int) $id_customer . '
			ORDER BY a.`id_address` DESC');
        return $last_address;
    }
}
