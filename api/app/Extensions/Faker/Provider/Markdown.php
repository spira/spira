<?php

namespace App\Extensions\Faker\Provider;

class Markdown extends \Faker\Provider\Base
{

    /**
     * @param int $elements
     * @return string
     */
    public function markdown($elements = null)
    {

        if (!$elements){
            $elements = rand(4, 15);
        }

        $elementTypes = ['para', 'title', 'blockquote'];

        $text = [];

        for($i = $elements; $i>=0; $i--){
            $text[] = $this->{$this->randomElement($elementTypes)}();
        }

        return implode("\n\n", $text);
    }

    private function title()
    {

        $level = rand(2, 4);

        return str_repeat('#', $level) .' '. $this->generator->realText(rand(10, 30));
    }

    private function blockquote()
    {
        return "> " . $this->generator->realText(rand(10, 100));
    }

    /**
     * @param int $length
     * @return string
     */
    private function para($length = 500)
    {
        $text = $this->generator->realText($length);

        $text = $this->wrapText($text, 0.1, '**'); //strong
        $text = $this->wrapText($text, 0.05, '_'); //em
        $text = $this->wrapText($text, 0.02, '[', '](' . $this->generator->url.')');

        return $text;
    }

    /**
     * @param $text
     * @param float $probability
     * @param $left
     * @param null $right
     * @return string
     */
    private function wrapText($text, $probability = 0.1, $left, $right = null)
    {
        if (!$right){
            $right = $left;
        }

        $words = explode(' ', $text);

        $transformed = array_map(function($word) use ($left, $right, $probability){

            $rand = mt_rand() / mt_getrandmax();

            if($rand >= $probability){
                return $word;
            }

            return "$left$word$right";

        }, $words);

        return implode(' ', $transformed);

    }



}