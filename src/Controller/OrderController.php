<?php

namespace App\Controller;

use App\Entity\City;
use App\Entity\Order;
use App\Service\Cart;
use App\Form\OrderType;
use App\Entity\OrderProducts;
use Symfony\Component\Mime\Email;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Service\StripePayment;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;

class OrderController extends AbstractController
{
    public function __construct(private MailerInterface $mailer){
    }


// final class OrderController extends AbstractController
// {
#region ORDER
    #[Route('/order', name: 'app_order')]
    public function index(Request $request, 
                          SessionInterface $session, 
                          ProductRepository $productRepo, 
                          EntityManagerInterface $entityManager, 
                          Cart $cart): Response
    {

        $data = $cart->getCart($session);

        $order =new Order();

        $form =$this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
                if(!empty($data['total'])) {
                    $totalPrice = $data['total'] + $order->getCity()->getShippingCost();

                    $order->setTotalPrice($totalPrice);
                    $order->setCreatedAt(new \DateTimeImmutable());
                    $order->setIsPaymentCompleted(0);
                    $entityManager->persist($order);
                    $entityManager->flush();
                    // dd($data['cart']); //voir ce qu'il y a dans une variable -> var dump and die

                    foreach($data['cart'] as $value) { // pour chaque elements dans le panier
                        $orderProduct = new OrderProducts(); 
                        $orderProduct->setOrder($order); 
                        $orderProduct->setProduct($value['product']); 
                        $orderProduct->setQuantity($value['quantity']);
                        $entityManager->persist($orderProduct);
                        $entityManager->flush();
                    }
                    if($order->isPayOnDelivery()){
                        $session->set('cart', []); // mise a jour du contenu du panier apres avoir flush

                        $html = $this->renderView('mail/orderConfirm.html.twig', [ //crée une nouvelle vue mail
                            'order'=>$order, // on recupere le $order apres le flush donc on a toute les infos
                        ]);
                        $email = (new Email()) //on importe la classe depuis symfony\component\mime\email
                        ->from('booksite@gmail.com') // mail de l'expediteur donc notre boutique ou nous meme
                        // ->to('to@gmail.com') // mail du receveur
                        ->to($order->getEmail())
                        ->subject('Order confirmation') // intitulé du mail
                        ->html($html);
                        $this->mailer->send($email);

                        return $this->redirectToRoute('app_order_message');
                    }
                }
            $paymentStripe = new StripePayment(); // on importe notre service stripe avec sa classe
            $shippingCost = $order->getCity()->getShippingCost();
            $paymentStripe->startPayment($data, $shippingCost, $order->getId()); // on importe le panier donc $data
            $stripeRedirectUrl = $paymentStripe->getStripeRedirectUrl();
            // dd( $stripeRedirectUrl);
            return $this->redirect($stripeRedirectUrl);
        }

        return $this->render('order/index.html.twig', [
            'form'=>$form->createView(),
            // 'total'=> $total,
            'total'=>$data['total'],
        ]);
    }
#endregion ORDER

#region MESSAGE OK
    #[Route('/order_message', name: 'app_order_message')] 
    public function orderMessage(): Response
    {
        return $this->render('order/order_message.html.twig');
    }
#endregion MESSAGE

#region CITY COST
    #[Route('/city/{id}/shipping/cost', name: 'app_city_shipping_cost')]
    public function cityShippingCost(City $city): Response
    {
        $cityShippingPrice = $city->getShippingCost();

        return new Response(json_encode(['status'=>200, 'message'=>'on', 'content'=>$cityShippingPrice]));
    }
#endregion CITY COST

#region EDITOR ORDERS
    #[Route('/editor/order/{type}/', name: 'app_orders_show')] 
    public function getAllOrder($type, OrderRepository $orderRepo, PaginatorInterface $paginator, Request $request): Response
    {

        if($type == 'is-completed'){
            $data = $orderRepo->findBy(['isCompleted'=>1],['id'=>'DESC']);
        } else if($type == 'pay-on-stripe-not-delivered'){
            $data = $orderRepo->findBy(['isCompleted'=>null, 'payOnDelivery'=>0,'isPaymentCompleted'=>1],['id'=>'DESC']);
        } else if($type == 'pay-on-stripe-is-delivered'){
            $data = $orderRepo->findBy(['isCompleted'=>1, 'payOnDelivery'=>0,'isPaymentCompleted'=>1],['id'=>'DESC']); 
        }else if($type == 'no-delivery'){
            $data = $orderRepo->findBy(['isCompleted'=>null,'payOnDelivery'=>0,'isPaymentCompleted'=>0],['id'=>'DESC']);
        }else if($type == 'all-orders'){
            $data = $orderRepo->findAll(['id'=>'DESC']);
        }

        // $orders= $orderRepo->findAll();
        $orders =$paginator->paginate(
            $data,
            $request->query->getInt('page', 1),
            5
        );

        return $this->render('order/orders.html.twig', [
            'orders'=>$orders,
        ]);
    }
#endregion EDITOR ORDERS

#region UPDATE 
    #[Route('/editor/order/{id}/is-completed/update', name: 'app_orders_is-completed-update')] 
    public function isCompletedUpdate(Request $request, $id, OrderRepository $orderRepo, EntityManagerInterface $entityManager): Response
    {
        $order = $orderRepo->find($id);
        $order->setIsCompleted(true);
        $entityManager->flush();
        $this->addFlash('success', 'The order have been updated !');
        return $this->redirect($request->headers->get('referer')); // redirige a la derniere page consulté
    }
#endregion UPDATE

#region DELETE
    #[Route('/editor/order/{id}/remove', name: 'app_orders_remove')] 
    public function removeOrder(Request $request, Order $order, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($order);
        $entityManager->flush();
        $this->addFlash('danger', 'The order have been deleted.');
        return $this->redirect($request->headers->get('referer'));
        // return $this->redirectToRoute('app_orders_show');
    }
#endregion DELETE
}