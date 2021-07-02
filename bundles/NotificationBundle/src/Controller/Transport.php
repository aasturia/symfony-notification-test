<?php

namespace corite\NotificationBundle\Controller;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Notifier\ChatterInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Notifier\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface as MailerTransportExceptionInterface;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Mime\Address;
use Symfony\Component\Notifier\Bridge\Telegram\TelegramOptions;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;


class Transport
{
    private $effects;
    private $mailer;
    private $chatter;
    private $logger;

    public function __construct(Rules $rules, MailerInterface $mailer, ChatterInterface $chatter, NotificationLogger $logger)
    {
        $this->effects = $rules->getEffects();
        $this->mailer = $mailer;
        $this->chatter = $chatter;
        $this->logger = $logger;
    }

    //send all notifications through transport according "type" field in rules effects
    public function sendNotification($data)
    {
        $this->logger->info("trying to send notifications");
        foreach ($this->effects as $effect) {
            $transportName = ucwords($effect->type);
            $sendFunction = "sendThrough" . $transportName;
            $this->$sendFunction($data, $effect);
        }
    }

    private function sendThroughSmtp($data, $effect)
    {
        $email = (new TemplatedEmail())
            ->from('alex.canzona@gmail.com')
            ->to(new Address($effect->recipient))
            ->subject('Тестовое письмо из Symfony')
            ->htmlTemplate('@Notification/emails/email' . ($effect->template_id) . '.html.twig')
            ->context([
                'projects' => $data,
                'username' => 'Asturia',
            ]);

        try {
            $this->mailer->send($email);
            $this->logger->info('The email was sent successfully  through smtp to recipient ' . $effect->recipient . ' with template' . $effect->template_id);
        } catch (MailerTransportExceptionInterface $e) {
            $this->logger->error('some trouble with sending email: ' . $e);
        }

    }

    private function sendThroughTelegram($data, $effect)
    {
        $loader = new FilesystemLoader(__DIR__.'/../../templates/telegram/');
        $twig = new Environment($loader);
        $template = $twig->load('telegram' . ($effect->template_id) . '.html.twig');
        $message = $template->render(['projects' => $data]);

        $chatMessage = new ChatMessage($message);
        $telegramOptions = (new TelegramOptions())
            ->chatId($effect->recipient)
            ->parseMode('html');
        $chatMessage->options($telegramOptions);
        
        try {
            $this->chatter->send($chatMessage);
            $this->logger->info('The notification was sent successfully through telegram to recipient '.$effect->recipient.' with template'.$effect->template_id);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('some error with sending to Telegram: '.$e);
        }

    }

}