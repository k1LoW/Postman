<?php

App::uses('RegisteredMail', 'Postman.Network/Email');
App::uses('HttpSocket', 'Network/Http');

class RegisteredMailTest extends CakeTestCase
{
  public function setUp() {
    $this->email = new RegisteredMail();
    $this->email->config(array(
      'transport' => 'Smtp',
      'from' => array('form@mailback.me' => 'from'),
      'to' => 'to+postman@fusic.co.jp',
      'host' => 'mail.mailback.me',
      'port' => 25,
      'timeout' => 30,
      'log' => false,
      'charset' => 'utf-8',
      'headerCharset' => 'utf-8',
    ));
  }

  public function tearDown() {
    unset($this->email);
  }

  /**
   * test_longMultibyteLine
   *
   */
  public function test_longMultibyteLine(){
    $message = '寿限無、寿限無五劫の擦り切れ海砂利水魚の水行末 雲来末 風来末食う寝る処に住む処藪ら柑子の藪柑子パイポパイポ パイポのシューリンガンシューリンガンのグーリンダイグーリンダイのポンポコピーのポンポコナーの長久命の長助寿限無、寿限無五劫の擦り切れ海砂利水魚の水行末 雲来末 風来末食う寝る処に住む処藪ら柑子の藪柑子パイポパイポ パイポのシューリンガンシューリンガンのグーリンダイグーリンダイのポンポコピーのポンポコナーの長久命の長助寿限無、寿限無五劫の擦り切れ海砂利水魚の水行末 雲来末 風来末食う寝る処に住む処藪ら柑子の藪柑子パイポパイポ パイポのシューリンガンシューリンガンのグーリンダイグーリンダイのポンポコピーのポンポコナーの長久命の長助寿限無、寿限無五劫の擦り切れ海砂利水魚の水行末 雲来末 風来末食う寝る処に住む処藪ら柑子の藪柑子パイポパイポ パイポのシューリンガンシューリンガンのグーリンダイグーリンダイのポンポコピーのポンポコナーの長久命の長助寿限無、寿限無五劫の擦り切れ海砂利水魚の水行末 雲来末 風来末食う寝る処に住む処藪ら柑子の藪柑子パイポパイポ パイポのシューリンガンシューリンガンのグーリンダイグーリンダイのポンポコピーのポンポコナーの長久命の長助寿限無、寿限無五劫の擦り切れ海砂利水魚の水行末 雲来末 風来末食う寝る処に住む処藪ら柑子の藪柑子パイポパイポ パイポのシューリンガンシューリンガンのグーリンダイグーリンダイのポンポコピーのポンポコナーの長久命の長助寿限無、寿限無五劫の擦り切れ海砂利水魚の水行末 雲来末 風来末食う寝る処に住む処藪ら柑子の藪柑子パイポパイポ パイポのシューリンガンシューリンガンのグーリンダイグーリンダイのポンポコピーのポンポコナーの長久命の長助';

    $expect = $message;
    $hash = sha1(time());
    $this->email->to('to+'.$hash.'@mailback.me');
    $this->email->subject('メールタイトル');
    $this->email->send($expect);

    sleep(5);

    $url = 'http://mailback.me/to/'.$hash.'.body';
    App::uses('HttpSocket', 'Network/Http');
    $HttpSocket = new HttpSocket();
    $results = $HttpSocket->get($url, array());

    $body = trim(str_replace(array("\r\n", "\n", ' '), '', $results->body));
    $message = trim(str_replace(array("\r\n", "\n", ' '), '', $message));

    $this->assertIdentical($body, $message);
  }
}
