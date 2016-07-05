<?php

namespace App\Controller;

use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class AppController implements ControllerProviderInterface
{
    use Application\SwiftmailerTrait;

    /**
     * @var Session
     */
    protected $session;

    public function __construct()
    {
        $this->session = new Session();
    }

    public function connect(Application $app)
    {
        /**
         * @type $controllers ControllerCollection
         */
        $controllers = $app['controllers_factory'];

        $paths = [
            '/' => 'indexAction',
            '/contact' => 'contactAction',
            '/portfolio' => 'portfolioAction'
        ];

        foreach ($paths as $path => $method) {
            $controllers->match($path, [$this, $method]);
        }

        return $controllers;
    }

    public function indexAction(Application $app)
    {
        /**
         * @type $twig \Twig_Environment
         */
        $twig = $app['twig'];

        return $twig->render('home.html.twig');
    }

    public function contactAction(Application $app)
    {
        /**
         * @type $twig \Twig_Environment
         */
        $twig = $app['twig'];

        // some default data for when the form is displayed the first time
        $data = array(
            'name' => '',
            'email' => '',
        );

        /**
         * @type $form_factory FormFactoryInterface
         */
        $form_factory = $app['form.factory'];

        $form = $form_factory->createBuilder(FormType::class, $data)
            ->add('name', TextType::class, [
                'label' => 'Name',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Your message',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 7,
                ]
            ])
            ->getForm();

        $form->handleRequest(Request::createFromGlobals());

        if ($form->isValid()) {
            /**
             * @type $session Session
             */
            $session = $app['session'];
            $flashBag = $session->getFlashBag();

            /**
             * Send email
             */
            $formData = $form->getData();
            $transport = \Swift_SmtpTransport::newInstance(getenv('SMTP_HOST'), getenv('SMTP_PORT'))
                ->setUsername(getenv('SMTP_USERNAME'))
                ->setPassword(getenv('SMTP_PASSWORD'));

            $mailer = \Swift_Mailer::newInstance($transport);
            $message = \Swift_Message::newInstance('[JPCaparas.com] Contact form message')
                ->setFrom([
                    $formData['email'] => $formData['name'],
                ])
                ->setTo([
                    'jp@jpcaparas.com',
                ])
                ->setBody($formData['message']);

            $emailsSent = $mailer->send($message);

            if ($emailsSent) {
                $flashBag->add('success_messages', 'Your message has been successfully sent.');
            } else {
                $flashBag->add('error_messages', 'Your message was not sent. Please try again later.');
            }

            // redirect somewhere
            return $app->redirect('/contact');
        }

        return $twig->render('contact.html.twig', [
            'form' => $form->createView()
        ]);
    }

    public function portfolioAction(Application $app)
    {
        /**
         * @type $twig \Twig_Environment
         */
        $twig = $app['twig'];

        return $twig->render('portfolio.html.twig');
    }
}