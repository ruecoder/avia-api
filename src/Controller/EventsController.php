<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/v1/callback")
 */
class EventsController extends AbstractController
{
    private $request; //сюда я положу реквест, пригодится
    private $em; //сюда посадим менеджера сущностей
    private $mailer;
    private $secret_key = 'a1b2c3d4e5f6a1b2c3d4e5f6'; //cекретный ключ, временно положил сюда

    public function __construct(EntityManagerInterface $entityManager,MailerInterface $mailer)
    {
        $request = Request::createFromGlobals(); //заполняем переменную реквестом
        $this->request = $request;
        $this->em = $entityManager; //заполянем EntityManager
        $this->mailer = $mailer;
    }

    /**
     * @Route("/events", name="events")
     */
    public function init()
    {
        $data = $this->request->getContent();
        $data = json_decode($data,true);

        if (!isset($data['data'])) {
            return $this->json([ //вернем ошибку
                'status' => 'error',
                'text' => 'data is NULL or incorrect',
            ]);
        } else {
            $data = $data['data'];
        }

        if ($data['secret_key'] !== $this->secret_key) {
            return $this->json([ //вернем ошибку
                'status' => 'error',
                'text' => 'Secret key novalid',
            ]);
        }

        $flight_id = (int)$data['flight_id'];

        if ($flight_id === 0) { //Если ид рейса 0, то
            return $this->json([ //вернем ошибку
                'status' => 'error',
                'text' => 'Empty flight_id',
            ]);
        }
        $flight_rep = $this->em->getRepository(\App\Entity\Flights::class); //Подключим репозиторий рейсов

        if (!$flight_rep->findOneBy(['id'=>$flight_id])) { //Если рейса не существует
            return $this->json([ //вернем ошибку
                'status' => 'error',
                'text' => 'Flight not found',
            ]);
        }

        if ($data['event'] === 'flight_ticket_sales_completed') {
            return $this->flight_ticket_sales_completed($flight_id);
        }

        if ($data['event'] === 'flight_canceled') {
            return $this->flight_canceled($flight_id);
        }

        return $this->json([ //вернем ошибку
            'status' => 'error',
            'text' => 'Noregister event',
        ]);
    }

    private function flight_ticket_sales_completed(int $flight_id) {
        $flight_rep = $this->em->getRepository(\App\Entity\Flights::class); //Подключим репозиторий рейсов
        $flight = $flight_rep->findOneBy(['id'=>$flight_id]); //здесь не вылетит ошибка, потому что $flight_id был проверен до того, как был передан в эту функцию
        $flight->setStatus('ticket_sales_completed');
        $this->em->flush();
        return $this->json([ //вернем результат
            'status' => 'ok',
        ]);
    }

    private function flight_canceled(int $flight_id) {
        $flight_rep = $this->em->getRepository(\App\Entity\Flights::class); //Подключим репозиторий рейсов
        $flight = $flight_rep->findOneBy(['id'=>$flight_id]); //здесь не вылетит ошибка, потому что $flight_id был проверен до того, как был передан в эту функцию
        $flight->setStatus('ticket_canceled'); //отменяем рейс

        $booking_rep = $this->em->getRepository(\App\Entity\Booking::class); //Подключим репозиторий рейсов
        $bookings = $booking_rep->findBy(['flight_id' => $flight_id]);

        $email = (new Email())
            ->from('noreply@avia-api.com')
            ->to('admin@evilcoder.ru');
        foreach ($bookings as $booking) {
            if ($booking->getStatus() !== 'canceled' && $booking->getStatus() !== 'cancel_buyed') {
                $email->addTo($booking->getEmail());
            }
        }

        $email->subject('Рейс отменён')
            ->text('Рейс который вы бронировали, отменён. Нам очень жаль.')
            ->html('<p>Рейс который вы бронировали, отменён. Нам очень жаль.</p>');
        $this->em->flush();
        $this->mailer->send($email);

        return $this->json([ //вернем результат
            'status' => 'ok',
        ]);
    }
}
