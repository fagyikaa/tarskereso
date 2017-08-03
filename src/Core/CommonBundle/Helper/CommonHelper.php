<?php

namespace Core\CommonBundle\Helper;


class CommonHelper {

    protected $languageRequirements;

    public function __construct($languageRequirements) {
        $this->languageRequirements = $languageRequirements;
    }

    /**
     * Returns in an array the supported language codes in lowercase.
     * 
     * @return array
     */
    public function getSupportedLanguagesArray() {
        $languageRequirements = explode('|', $this->languageRequirements);
        return array_map('strtolower', $languageRequirements);
    }

    /**
     * Returns in an array the supported language codes in lowercase and the format forms require.
     * 
     * @return array
     */
    public function getSupportedLanguagesArrayForForm() {
        $languageRequirements = explode('|', $this->languageRequirements);
        $languagesArrayForForm = array();
        foreach (array_map('strtoupper', $languageRequirements) as $language) {
            $languagesArrayForForm[$language] = $language;
        }

        return $languagesArrayForForm;
    }
   
    /**
     * Returns the depth of the given array. This method is safe even for recursive array.
     * 
     * @param array $array
     * @return int
     */
    public function getArrayDepth(array $array) {
        $max_indentation = 1;

        $array_str = print_r($array, true);
        $lines = explode("\n", $array_str);

        foreach ($lines as $line) {
            $indentation = (strlen($line) - strlen(ltrim($line))) / 4;

            if ($indentation > $max_indentation) {
                $max_indentation = $indentation;
            }
        }

        return ceil(($max_indentation - 1) / 2) + 1;
    }

}
