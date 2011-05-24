<?php
/*
 * 4/24/2010
 * NumberTranslator is a helper class for PHP that translates words into integers.
 * The words must be a number spelled out. For example 'forty five' would translate to 45 and so on.
 *
 * Fairly exhaustive user input validation implemented.
 *
 * (c) Jason Rikard <jsnrkd@gmail.com>
 *
 */



class NumberTranslator
{
    /**
     * Numbers 0 - 9
     * @access private
     * @var array
     **/
    private static $basicNumbers = array(
        'zero' => 0, 'one' => 1, 'two' => 2, 'three' => 3, 'four' => 4,
        'five' => 5, 'six' => 6, 'seven' => 7, 'eight' => 8, 'nine' => 9
    );

    /**
     * These numbers stand alone in verbal phrases and are not modified except by negative
     * @access private
     * @var array
     */
    private static $soloNumbers = array(
        'ten' => 10, 'eleven' => 11, 'twelve' => 12, 'thirteen' => 13, 'fourteen' => 14,
        'fifteen' => 15, 'sixteen' => 16, 'seventeen' => 17, 'eighteen' => 18, 'nineteen' => 19
    );

    /**
     * Numbers that can be combined with basic numbers
     * @access private
     * @var array
     */
    private static $helperNumbers = array(
        'twenty' => 20, 'thirty' => 30, 'forty' => 40, 'fifty' => 50, 'sixty' => 60,
        'seventy' => 70, 'eighty' => 80, 'ninety' => 90
    );

    /**
     * Each modifier can only appear once in the input
     * @access private
     * @var array
     */
    private static $oneTimeModifiers = array(
        'negative' => -1, 'thousand' => 1000, 'million' => 1000000
    );

    /**
     * Modifiers can appear multiple times in input but not next to each other
     * @access private
     * @var array
     */
    private static $modifiers = array(
        'hundred' => 100
    );

    /**
     * The array of words from the user input
     * @access private
     * @var array
     */
    private $words;

    /**
     * The calculated result
     * @access private
     * @var int
     */
    private $result;

    function __construct()
    {
    }

    /**
     * Convert the array of number words to integers calculate the integer value
     * @return void
     */
    public function translateToInteger()
    {
        $computedValue = 0;
        $computedStack = array();
        for ($i = 0; $i < count($this->words); $i++) {

            //See if the word is 0-9, or basicNumber
            if (self::isBasicNumber($this->words[$i])) {
                $computedValue += self::$basicNumbers[$this->words[$i]];
            }
                //See if the number needs to be modified
            else if (self::isModifier($this->words[$i])) {
                $computedValue *= self::$modifiers[$this->words[$i]];
            }
                //See if the number needs to be modified by a one time modifier
            else if (self::isOneTimeModifier($this->words[$i])) {
                $computedValue *= self::$oneTimeModifiers[$this->words[$i]];
                //Force order of operations by putting computed value for later arithmetic
                $computedStack[] = $computedValue;
                $computedValue = 0;
            }
                //See if the word is a solo number
            else if (self::isSoloNumber($this->words[$i])) {
                $computedValue += self::$soloNumbers[$this->words[$i]];
            }
                //See if the word is a helper number
            else if (self::isHelperNumber($this->words[$i])) {
                $computedValue += self::$helperNumbers[$this->words[$i]];
            }
            else {
                throw new Exception('Unknown user input detected!');
            }
        }
        //Finish forcing order of operations and calculate the final value
        foreach ($computedStack as $value) {
            $computedValue += $value;
        }
        //Make the number negative if required
        if ($this->words[0] == 'negative') {
            $computedValue *= -1;
        }
        //Store result in member variable
        $this->result = $computedValue;
    }

    /**
     * Helper function: Checks if a word is a basic number
     * @static
     * @param  $word string
     * @return bool
     */
    private static function isBasicNumber($word)
    {
        if (array_key_exists($word, self::$basicNumbers)) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Helper function: Checks if a word is a modifier
     * @static
     * @param  $word string
     * @return bool
     */
    private static function isModifier($word)
    {
        if (array_key_exists($word, self::$modifiers)) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Helper function: Checks if a word is a one time modifier
     * @static
     * @param  $word string
     * @return bool
     */
    private static function isOneTimeModifier($word)
    {
        if (array_key_exists($word, self::$oneTimeModifiers)) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Helper function: Checks if a word is a helper number
     * @static
     * @param  $word string
     * @return bool
     */
    private static function isHelperNumber($word)
    {

        if (array_key_exists($word, self::$helperNumbers)) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Helper function: Checks if a word is a solo number
     * @static
     * @param  $word string
     * @return bool
     */
    private static function isSoloNumber($word)
    {
        if (array_key_exists($word, self::$soloNumbers)) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Setter for $words. Sets it to a sanatized array by verifying user input
     * @param  $wordPhrase
     * @return void
     */
    public function setWords($wordPhrase)
    {
        $wordPhrase = self::sanitizeInput($wordPhrase);
        $this->words = str_word_count($wordPhrase, 1); //Creates alphabetic word only array

        try {
            self::validateUserInput($this->words);
        }
        catch (Exception $e) {
            exit($e->getMessage() . "\n");
        }
    }

    /**
     * Getter for $result
     * @return int
     */
    public function getResult()
    {
        return $this->result;
    }


    /**
     * Attempt to do basic sanitizing by checking against RegEx for letters only and then formats the text.
     * @static
     * @param  $input string
     * @return string
     */
    private static function sanitizeInput($input)
    {
        //RegEx matches letters only.
        if (!preg_match("/[^a-zA-Z ]/", $input)) {
            //throw new Exception('Only uppercase and lowercase letters are allowed');
        }
        $input = strtolower($input);
        $input = trim($input);

        return $input;
    }

    /**
     * Attempts to verify that the user input for validity. Checks word duplication, typos, and word order.
     * @static
     * @throws Exception
     * @param  $input array
     * @return bool
     */
    private static function validateUserInput($input)
    {
        $wordTypeStack = array();
        $oneTimeModifierArray = array();
        $current = '';
        //The first word must be one of these three categories
        if (self::isBasicNumber($input[0]) || self::isSoloNumber($input[0]) || self::isHelperNumber($input[0])
            || $input[0] == 'negative') {
            foreach ($input as $key => $word) {
                //Basic numbers are allowed after everything except solo numbers and itself.
                if (self::isBasicNumber($word) && $current != 'solo') {
                    //Make sure if zero is in the input that it is the only word in the array
                    if ($word == 'zero' && count($input) > 1) {
                        throw new Exception('You may only type zero by itself.');
                    }
                    $current = 'basic';
                }
                    //Modifiers are only allowed after basic numbers
                else if (self::isModifier($word) && $current == 'basic') {
                    $current = 'modifier';
                }
                    //One time modifiers are allowed after everything but itself and must be unique.
                else if (self::isOneTimeModifier($word)) {
                    //If the one time modifier was already used, throw error
                    if (in_array($word, $oneTimeModifierArray)) {
                        throw new Exception('One time modifiers can only be used once!');
                    }
                        //Make sure that the negative word only appears as the first word in the array
                    else if ($word == 'negative' && $key != 0) {
                        throw new Exception('Negative can only be placed at the beginning of input');
                    }
                        //Make sure the order of the one time modifiers makes sense. Example: Million before thousand
                    else if (count($oneTimeModifierArray) > 0) {
                        //If the previous one time modifier is less than the current there is an order problem
                        if (self::$oneTimeModifiers[end($oneTimeModifierArray)] < self::$oneTimeModifiers[$word]) {
                            throw new Exception('One time modifiers out of order.');
                        }
                    }
                        //Don't add negative to the checking array because the value is -1
                    else if ($word != 'negative') {
                        $oneTimeModifierArray[] = $word;
                    }
                    //Everything is fine
                    $current = 'oneTime';

                }
                    //Helper numbers are only allowed after modifiers and one time modifiers unless it's the first word
                else if (self::isHelperNumber($word)) {
                    //If the position is not the beginning make sure it has correct placement
                    if (count($wordTypeStack) != 0) {
                        if ($current == 'oneTime' || $current == 'modifier') {
                            $current = 'helper';
                        }
                        else {
                            throw new Exception('Check the order of the user input and try again');
                        }
                    }
                        //It's okay if a helper word appears at the beginning
                    else {
                        $current = 'helper';
                    }
                }
                    //Solo numbers are only allowed after modifiers and one time modifiers unless it's the first word
                else if (self::isSoloNumber($word)) {
                    if (count($wordTypeStack) != 0) {
                        if ($current == 'oneTime' || $current == 'modifier') {
                            $current = 'solo';
                        }
                        else {
                            throw new Exception('Check the order of the user input and try again');
                        }
                    }
                    else {
                        $current = 'solo';
                    }
                }
                else {
                    throw new Exception("Unknown word or placement detected at '$word'");
                }
                //Check to see if two words of the same type are next to each other in the array
                if (end($wordTypeStack) != $current) {
                    $wordTypeStack[] = $current;

                }
                else {
                    throw new Exception('Duplicate word type detected!');
                }
            }

        }
        else {
            throw new Exception('The first word is not valid.');
        }
        return true;
    }
}

?>
