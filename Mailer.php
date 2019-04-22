<?php

namespace boundstate\mailgun;

use Yii;
use yii\base\InvalidConfigException;
use yii\mail\BaseMailer;
use \Mailgun\Mailgun;

/**
 * Mailer implements a mailer based on Mailgun.
 *
 * To use Mailer, you should configure it in the application configuration like the following,
 *
 * ~~~
 * 'components' => [
 *     ...
 *     'mailer' => [
 *         'class' => 'boundstate\mailgun\Mailer',
 *         'key' => 'key-example',
 *         'domain' => 'mg.example.com',
 *     ],
 *     ...
 * ],
 * ~~~
 *
 * To send an email, you may use the following code:
 *
 * ~~~
 * Yii::$app->mailer->compose('contact/html', ['contactForm' => $form])
 *     ->setFrom('from@domain.com')
 *     ->setTo($form->email)
 *     ->setSubject($form->subject)
 *     ->send();
 * ~~~
 */
class Mailer extends BaseMailer
{
    /**
     * @var string message default class name.
     */
    public $messageClass = 'boundstate\mailgun\Message';

    /**
     * @var string Mailgun API credentials.
     */
    public $key;

    /**
     * @var string Mailgun domain.
     */
    public $domain;

    /**
     * @var Mailgun Mailgun instance.
     */
    private $_mailgun;

    /**
     * @return Mailgun Mailgun instance.
     */
    public function getMailgun()
    {
        if (!is_object($this->_mailgun)) {
            $this->_mailgun = $this->createMailgun();
        }

        return $this->_mailgun;
    }

    /**
     * @inheritdoc
     */
    protected function sendMessage($message)
    {
        Yii::info('Sending email', __METHOD__);
	$to = $message->getTo();
	$subject = $message->getSubject();
	$body = $message->getMessageBuilder()->getMessage();
	$from = $message->getFrom();
	$fromKeys = array_keys( $from );
	$fromEmail = $fromKeys[ 0 ];
	$params = [
		'from'    => $fromEmail,
		'to'      => $to[0],
		'subject' => $subject,
		'html'    => $body['html']
	];
	$this->getMailgun()->messages()->send($this->domain, $params);

        return true;
    }

    /**
     * Creates Mailgun instance.
     * @return Mailgun Mailgun instance.
     * @throws InvalidConfigException if required params are not set.
     */
    protected function createMailgun()
    {
        if (!$this->key) {
            throw new InvalidConfigException('Mailer::key must be set.');
        }
        if (!$this->domain) {
            throw new InvalidConfigException('Mailer::domain must be set.');
        }
        return Mailgun::create($this->key);
    }
}
