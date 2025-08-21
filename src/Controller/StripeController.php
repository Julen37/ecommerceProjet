<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Stripe;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class StripeController extends AbstractController
{
    #[Route('/pay/success', name: 'app_stripe_success')]
    public function success(SessionInterface $session): Response
    {
        $session->set('cart', []);

        return $this->render('stripe/index.html.twig', [
            'controller_name' => 'StripeController',
        ]);
    }

    #[Route('/pay/cancel', name: 'app_stripe_cancel')]
    public function cancel(): Response
    {
        return $this->render('stripe/cancel.html.twig', [
            'controller_name' => 'StripeController',
        ]);
    }

    #[Route('/stripe/notify', name: 'app_stripe_notify')]
    public function stripeNotify(Request $request, 
                                OrderRepository $orderRepo,
                                EntityManagerInterface $entityManager): Response
    {
        Stripe::setApiKey($_SERVER['STRIPE_SECRET_KEY']);
        // file_put_contents("log.txt", ""); 
        $endpoint_secret = $_SERVER['STRIPE_SECRET_KEY_WEBHOOK'];
        $payload = $request->getContent();
        // file_put_contents("log.txt", $payload, FILE_APPEND);
        $sigHeader = $request->headers->get('Stripe-Signature');
        $event = null;

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sigHeader, $endpoint_secret
            );
        // file_put_contents("log.txt", "try ok", FILE_APPEND);

        } catch(\UnexpectedValueException $e){
            return new Response('Invalid payload', 400);

        } catch(\Stripe\Exception\SignatureVerificationException $e){
            return new Response('Invalid signature', 400);
        }
        // file_put_contents("log.txt", $event->type, FILE_APPEND);

        switch($event->type){
            case 'payment_intent.succeeded':
                // file_put_contents("log.txt", "succeeded", FILE_APPEND);
                $paymentIntent = $event->data->object;

                $fileName = 'stripe-detail-'.uniqid().'.txt';
                // file_put_contents($fileName, $paymentIntent);
                $orderId = $paymentIntent->metadata->orderid;
                // $order = $orderRepo->find($orderId);
                $order = $orderRepo->findOneBy(["id"=>$orderId]);

                $cartPrice = $order->getTotalPrice();
                // $stripeTotalAmount = $paymentIntent->amount/100;
                $stripeTotalAmount = $paymentIntent->amount;

                if($cartPrice*100 == $stripeTotalAmount){
                    $order->setIsPaymentCompleted(1); // true ou 1 fonctionne ?
                    // file_put_contents($fileName, $orderId);
                    $entityManager->flush();
                }

                break;
            case 'payment_method.attached':
                $paymentMethod =$event->data->object;
                break;
            default:
                break;
        }
        
        return new Response('Event success', 200);
    }
}
