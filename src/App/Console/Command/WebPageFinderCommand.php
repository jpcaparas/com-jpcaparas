<?php

namespace App\Console\Command;

use Dotenv\Dotenv;
use Dotenv\Exception\ValidationException;
use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class WebPageFinderCommand extends Command {
	protected function configure() {
		$this
			->setName( 'app:webpagefinder' )
			->setDescription( 'Check for the existence of a string of text in a webpage.' )
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
				'notify',
				null,
				InputOption::VALUE_REQUIRED,
				'Notify a recipient via email if specified text exists on the webpage.'
			);
	}

	protected function execute( InputInterface $input, OutputInterface $output ) {
		try {
			$dotEnv = new Dotenv( __DIR__ . '/../../../..' );
			$dotEnv->load();
			$dotEnv->required( [
				'SMTP_HOST',
				'SMTP_PORT',
				'SMTP_USERNAME',
				'SMTP_PASSWORD'
			] );
		}
		catch ( ValidationException $e ) {
			$output->writeln( 'Mailer error: ' . $e->getMessage() );

			return;
		}

		$url  = $input->getArgument( 'url' );
		$text = $input->getArgument( 'text' );

		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			$output->writeln( 'Please enter a valid URL.' );

			return;
		}

		if ( trim( $text ) === '' ) {
			$output->writeln( 'Please enter the text to find.' );

			return;
		}

		$client   = new Client();
		$response = $client->get( $url );
		$haystack = $response->getBody()->getContents();

		$isFound = strpos( $haystack, $text ) !== false;

		if ( $isFound ) {
			$message = sprintf(
				'The text "%1$s" has been found on the body of this webpage: %2$s',
				$text,
				$url
			);

			$notify = $input->getOption( 'notify' );

			if ( ! is_null( $notify ) && filter_var( $notify, FILTER_VALIDATE_EMAIL ) ) {
				$this->notify( $notify, $url, $text );

				$message .= sprintf(
					PHP_EOL . 'The recipient {%1$s} has also been sent an email notification.',
					$notify
				);
			}
		} else {
			$message = sprintf(
				'The text "%1$s" could not be found on the body of this webpage: %2$s',
				$text,
				$url
			);
		}

		$output->writeln( $message );
	}

	/**
	 * @param $recipientEmail
	 * @param $url
	 * @param $text
	 *
	 * @return int
	 */
	public function notify( $recipientEmail, $url, $text ) {
		$transport = \Swift_SmtpTransport::newInstance( getenv( 'SMTP_HOST' ), getenv( 'SMTP_PORT' ) )
		                                 ->setUsername( getenv( 'SMTP_USERNAME' ) )
		                                 ->setPassword( getenv( 'SMTP_PASSWORD' ) );

		$subject = sprintf(
			'[%1$s] Text found on %2$s',
			'jpcaparas.com',
			$url
		);

		$body = sprintf(
			'The text "%1$s" has been found on the body of this webpage: %2$s',
			$text,
			$url
		);

		$mailer = \Swift_Mailer::newInstance( $transport );

		$message = \Swift_Message::newInstance( $subject )
		                         ->setFrom( [
			                         'jp@jpcaparas.com' => 'JP Caparas',
		                         ] )
		                         ->setTo( [
			                         $recipientEmail
		                         ] )
		                         ->setBody( $body );

		$result = $mailer->send( $message );

		return $result;
	}
}