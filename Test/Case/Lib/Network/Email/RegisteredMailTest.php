<?php

App::uses('RegisteredMail', 'Postman.Network/Email');

class RegisteredMailTest extends CakeTestCase
{
    public function setUp() {
        $this->email = new RegisteredMail();
    }

    public function tearDown() {
    }

    /**
     * testWordWrap
     *
     */
    public function testWordwrap(){
        $str = '<!DOCTYPE html><html lang="en" class=""><head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# object: http://ogp.me/ns/object# article: http://ogp.me/ns/article# profile: http://ogp.me/ns/profile#"><meta charset="utf-8"></html>';
        $wrapped = explode("\n", $this->email->mb_wordwrap($str, 19, "\n", true));
        $this->assertIdentical($wrapped[0], '<!DOCTYPE html>');
    }
}
