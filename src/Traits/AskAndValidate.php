<?php

/*
 * This file is part of the Speedwork package.
 *
 * (c) Sankar <sankar.suda@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Speedwork\Console\Traits;

use Symfony\Component\Console\Question\Question;

/**
 * A trait to add validation to console questions.
 *
 * @author sankar <sankar.suda@gmail.com>
 **/
trait AskAndValidate
{
    /**
     * Asks a question and validates the response.
     *
     * @param string   $question
     * @param array    $choices
     * @param callback $validator
     * @param mixed    $default
     * @param bool     $secret
     *
     * @return string
     */
    public function askAndValidate($question, array $choices, $validator, $default = null, $secret = false)
    {
        $question = new Question($question, $default);

        if ($secret) {
            $question->setHidden(true);
        }

        if (count($choices)) {
            $question->setAutocompleterValues($choices);
        }

        $question->setValidator($validator);

        return $this->getOutput()->askQuestion($question);
    }

    /**
     * Asks a question and validates the secret response.
     *
     * @param string   $question
     * @param array    $choices
     * @param callback $validator
     * @param mixed    $default
     *
     * @return string
     */
    public function askSecretAndValidate($question, array $choices, $validator, $default = null)
    {
        return $this->askAndValidate($question, $choices, $validator, $default, true);
    }
}
