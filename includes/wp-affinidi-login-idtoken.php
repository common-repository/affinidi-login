<?php

// ABSPATH prevent public user to directly access your .php files through URL.
defined('ABSPATH') or die('No script kiddies please!');

class Affinidi_Login_IDToken {

    public function extract_claim($idToken, $field, $isCustom = false)
    {
        $claim_value = "";

        if ($isCustom) {
            $claim_value = isset($idToken['custom'][$field]) ? $idToken['custom'][$field] : "";    
        } else {
            $claim_value = isset($idToken[$field]) ? $idToken[$field] : "";
        }

        return $field == "email" ? sanitize_email($claim_value) : sanitize_text_field($claim_value);
    }

    public function extract_user_info($info) 
    {

        // extract user info
        $email = $this->extract_claim($info, 'email');
        $firstName = $this->extract_claim($info, 'given_name');
        $lastName = $this->extract_claim($info, 'family_name');
        $displayName = trim("{$firstName} {$lastName}");
    
        return array(
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'display_name' => $displayName
        );
    
    }
    
    public function extract_contact_info($info) 
    {
        // get list of countries for transformation
        include_once(AFFINIDI_PLUGIN_DIR . '/templates/countries-list.php');
        // extract phone number from top level
        $phoneNumber = $this->extract_claim($info, 'phone_number');
        // do we have the address info 
        if (!isset($info['address'])) {
            // return empty
            return array(
                'address_1' => '',
                'city' => '',
                'state' => '',
                'postcode' => '',
                'country' => '',
                'phone' => $phoneNumber
            );
        }

        // extract user info
        $streetAddress = $this->extract_claim($info['address'], 'street_address');
        $locality = $this->extract_claim($info['address'], 'locality');
        $region = $this->extract_claim($info['address'], 'region');
        $postalCode = $this->extract_claim($info['address'], 'postal_code');
        $country = $this->extract_claim($info['address'], 'country');
    
        // get the country code
        $country = sanitize_text_field(array_search($country, $countries_list));
    
        return array(
            'address_1' => $streetAddress,
            'city' => $locality,
            'state' => $region,
            'postcode' => $postalCode,
            'country' => $country,
            'phone' => $phoneNumber
        );
    }

}