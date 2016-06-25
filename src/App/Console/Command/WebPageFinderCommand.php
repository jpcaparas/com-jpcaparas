<?php

namespace App\Console\Command;

use Dotenv\Dotenv;
use Dotenv\Exception\ValidationException;
use Goutte\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @see http://stackoverflow.com/questions/27936323/debugging-laravel-artisan-from-phpstorm-with-homestead
 *
 * Class WebPageFinderCommand
 */
class WebPageFinderCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('app:webpagefinder')
            ->setDescription('Check for the existence of a string of text in a webpage.')
            ->addArgument(
                'url',
                InputArgument::REQUIRED,
                'What is the URL of the webpage where you want to find a string of text?'
            )
            ->addArgument(
                'text',
                InputArgument::REQUIRED,
                'What is the string of text you want to search?'
            )
            ->addOption(
                'notify-if-exists',
                null,
                InputOption::VALUE_OPTIONAL,
                'Notify a recipient via email if specified text exists.'
            )
            ->addOption(
                'notify-if-not-exists',
                null,
                InputOption::VALUE_OPTIONAL,
                'Notify a recipient instead if the text does not exist.'
            );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $dotEnv = new Dotenv(__DIR__ . '/../../../..');
            $dotEnv->load();
            $dotEnv->required([
                'SMTP_HOST',
                'SMTP_PORT',
                'SMTP_USERNAME',
                'SMTP_PASSWORD',
            ]);
        } catch (ValidationException $e) {
            $output->writeln('Mailer error: ' . $e->getMessage());

            return;
        }

        $url = $input->getArgument('url');
        $text = $input->getArgument('text');

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $output->writeln('Please enter a valid URL.');

            return;
        }

        if (trim($text) === '') {
            $output->writeln('Please enter the text to find.');

            return;
        }

        $client = new Client();
        $request = $client->request('GET', $url);
        $haystack = $request->filter('body')->html();
        $textFound = strpos(strtolower($haystack), strtolower($text)) !== false;
        $notifyIfExists = $input->getOption('notify-if-exists');
        $notifyIfNotExists = $input->getOption('notify-if-not-exists');
        $recipient = null;

        if ($textFound) {
            $message = sprintf(
                'The text "%1$s" has been found on the body of this webpage: %2$s',
                $text,
                $url
            );

        } else {
            $message = sprintf(
                'The text "%1$s" has not been found on the body of this webpage: %2$s',
                $text,
                $url
            );
        }


        if ($textFound && $notifyIfExists && filter_var($notifyIfExists, FILTER_VALIDATE_EMAIL)) {
            $this->notify($notifyIfExists, "[jpcaparas.com] Text \"{$text}\" found on {$url}", $message);
            $recipient = $notifyIfExists;
        }

        if (!$textFound && $notifyIfNotExists && filter_var($notifyIfNotExists, FILTER_VALIDATE_EMAIL)) {
            $this->notify($notifyIfNotExists, "[jpcaparas.com] Text \"{$text}\" NOT found on {$url}", $message);
            $recipient = $notifyIfNotExists;
        }

        if (!is_null($recipient)) {
            $message .= sprintf(
                PHP_EOL . 'The recipient {%1$s} has also been sent an email notification.',
                $recipient
            );
        }

        $output->writeln($message);
    }

    /**
     * @param string $recipientEmail
     * @param string $subject
     * @param string $message
     *
     * @return int
     */
    public function notify($recipientEmail, $subject, $message)
    {
        $transport = \Swift_SmtpTransport::newInstance(getenv('SMTP_HOST'), getenv('SMTP_PORT'))
            ->setUsername(getenv('SMTP_USERNAME'))
            ->setPassword(getenv('SMTP_PASSWORD'));

        $mailer = \Swift_Mailer::newInstance($transport);

        $message = \Swift_Message::newInstance($subject)
            ->setFrom([
                'jp@jpcaparas.com' => 'JP Caparas',
            ])
            ->setTo([
                $recipientEmail,
            ])
            ->setBody($message);

        $result = $mailer->send($message);

        return $result;
    }
}
