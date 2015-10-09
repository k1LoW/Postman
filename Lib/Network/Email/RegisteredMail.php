<?php
if (file_exists(dirname(__FILE__) . '/../../../vendor/autoload.php')) {
    require_once(dirname(__FILE__) . '/../../../vendor/autoload.php');
}
App::uses('CakeEmail', 'Network/Email');
App::uses('RegisteredMailLog', 'Postman.Model');

class RegisteredMail extends CakeEmail
{
    public $rawSubject = '';
    public $RegisteredMailLog = null;

    public function __construct($config = null)
    {
        parent::__construct($config);
        $s = new Smarty();
        $log = Configure::read('Postman.writeLog');
        if ($log) {
            $this->RegisteredMailLog = ClassRegistry::init('RegisteredMailLog');
        }
        include_once SMARTY_PLUGINS_DIR . 'shared.mb_wordwrap.php';
    }

    public function send($content = null)
    {
        try {
            $mode = Configure::read('Postman.censorship.mode');
            if ($mode) {
                $currentConfig = $this->config();
                $this->config(Configure::read('Postman.censorship.config'));
                $this->_cc = array();
                $this->_bcc = array();
            }
            $contents = parent::send($content);
            $this->writeLog(true, $contents);
            if ($mode) {
                $this->config($currentConfig);
            }
        } catch (SocketException $e) {
            $this->writeLog($e->getMessage());
            throw new SocketException($e->getMessage());
        }
        return $contents;
    }

    public function subject($subject = null)
    {
        $this->rawSubject = $subject;
        return parent::subject($subject);
    }

    protected function _wrap($message, $wrapLength = CakeEmail::LINE_LENGTH_MUST)
    {
        if (strlen($message) === 0) {
            return array('');
        }
        $message = str_replace(array("\r\n", "\r"), "\n", $message);
        $charset = $this->charset();
        switch(strtolower($charset)) {
        case 'utf-8':
            $wordByte = 4;
            break;
        case 'iso-2022-jp-ms':
        case 'iso-2022-jp':
            $wordByte = 9;
            break;
        default:
            $wordByte = 2;
            break;
        }

        $lines = explode("\n", $message);
        $formatted = array();
        $cut = ($wrapLength == CakeEmail::LINE_LENGTH_MUST);

        foreach ($lines as $line) {
            if (empty($line)) {
                $formatted[] = '';
                continue;
            }
            if ($this->strlen($line) < $wrapLength) {
                $formatted[] = $line;
                continue;
            }
            if (!preg_match('/<[a-z]+.*>/i', $line)) {
                $formatted = array_merge(
                    $formatted,
                    explode("\n", $this->mb_wordwrap($line, floor($wrapLength / $wordByte), "\n", $cut))
                );
                continue;
            }

            $tagOpen = false;
            $tmpLine = $tag = '';
            $tmpLineLength = 0;
            for ($i = 0, $count = $this->strlen($line); $i < $count; $i++) {
                $char = $line[$i];
                if ($tagOpen) {
                    $tag .= $char;
                    if ($char === '>') {
                        $tagLength = $this->strlen($tag);
                        if ($tagLength + $tmpLineLength < $wrapLength) {
                            $tmpLine .= $tag;
                            $tmpLineLength += $tagLength;
                        } else {
                            if ($tmpLineLength > 0) {
                                $formatted = array_merge(
                                    $formatted,
                                    explode("\n", $this->mb_wordwrap(trim($tmpLine), floor($wrapLength / $wordByte), "\n", $cut))
                                );
                                $tmpLine = '';
                                $tmpLineLength = 0;
                            }
                            if ($tagLength > $wrapLength) {
                                $formatted[] = $tag;
                            } else {
                                $tmpLine = $tag;
                                $tmpLineLength = $tagLength;
                            }
                        }
                        $tag = '';
                        $tagOpen = false;
                    }
                    continue;
                }
                if ($char === '<') {
                    $tagOpen = true;
                    $tag = '<';
                    continue;
                }
                if ($char === ' ' && $tmpLineLength >= $wrapLength) {
                    $formatted[] = $tmpLine;
                    $tmpLineLength = 0;
                    continue;
                }
                $tmpLine .= $char;
                $tmpLineLength++;
                if ($tmpLineLength === $wrapLength) {
                    $nextChar = $line[$i + 1];
                    if ($nextChar === ' ' || $nextChar === '<') {
                        $formatted[] = trim($tmpLine);
                        $tmpLine = '';
                        $tmpLineLength = 0;
                        if ($nextChar === ' ') {
                            $i++;
                        }
                    } else {
                        $lastSpace = strrpos($tmpLine, ' ');
                        if ($lastSpace === false) {
                            continue;
                        }
                        $formatted[] = trim(substr($tmpLine, 0, $lastSpace));
                        $tmpLine = substr($tmpLine, $lastSpace + 1);

                        $tmpLineLength = $this->strlen($tmpLine);
                    }
                }
            }
            if (!empty($tmpLine)) {
                $formatted[] = $tmpLine;
            }
        }
        $formatted[] = '';
        return $formatted;
    }

    /**
     * writeLog
     *
     */
    public function writeLog($status = true, $contents = null)
    {
        if (empty($this->RegisteredMailLog)) {
            return;
        }

        $data = array();
        $headers = array(
            'from', 'sender', 'replyTo', 'readReceipt', 'returnPath',
            'to', 'cc', 'bcc', 'subject');
        foreach ($headers as $header) {
            $var = '_' . $header;
            $data[Inflector::underscore($header)] = $this->{$var};
        }
        $data['subject_raw'] = $this->rawSubject;
        $data['headers'] = $this->getHeaders($headers);
        $data['message'] = $this->message();

        if (!empty($contents['headers'])) {
            $data['headers_sent'] = $contents['headers'];
        }
        if (!empty($contents['message'])) {
            $data['message_sent'] = $contents['message'];
        }

        if ($status === true) {
            $data['status'] = 'sent';
        } else {
            $data['status'] = 'error';
            $data['error_message'] = $status;
        }

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = json_encode($value);
            }
        }

        $this->RegisteredMailLog->create();
        return $this->RegisteredMailLog->save($data);
    }

    // @see http://www.cpa-lab.com/tech/0144
    protected function strlen($string)
    {
        return strlen(bin2hex($string)) / 2;
    }

    public function mb_wordwrap($string, $width = 75, $break = "\n", $cut = false)
    {
        return smarty_mb_wordwrap($string, $width, $break, $cut);
    }
}
