<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/v1")
 */
class ApiController extends AbstractController
{
    private $request; //сюда я положу реквест, пригодится
    private $em; //сюда посадим менеджера сущностей

    public function __construct(EntityManagerInterface $entityManager)
    {
        $request = Request::createFromGlobals(); //заполняем переменную реквестом
        $this->request = $request;
        $this->em = $entityManager; //заполянем EntityManager
    }

    /**
     * @Route("/Booking", name="booking")
     */
    public function booking()
    {
        $flight_id = (int)$this->request->get('flight_id', 0);

        if ($flight_id === 0) { //Если ид рейса 0, то
            return $this->json([ //вернем ошибку
                'status' => 'error',
                'text' => 'Empty flight_id',
            ]);
        }
        $flight_rep = $this->em->getRepository(\App\Entity\Flights::class); //Подключим репозиторий рейсов
        $flight = $flight_rep->findOneBy(['id'=>$flight_id]);

        if (!$flight) { //Если рейса не существует
            return $this->json([ //вернем ошибку
                'status' => 'error',
                'text' => 'Flight not found',
            ]);
        }

        if ($flight->getStatus() !== 'wait') { //Если рейс не в ожидании, т.е. отменен, закрыт или еще хз что, то
            return $this->json([ //вернем ошибку
                'status' => 'error',
                'text' => 'Flight is closed',
            ]);
        }

        $seat = $this->request->get('seat', 0);

        if ($seat === 0) { //Если посадочное место равно 0, то
            return $this->json([ //вернем ошибку
                'status' => 'error',
                'text' => 'Empty seat',
            ]);
        }

        $booking_rep = $this->em->getRepository(\App\Entity\Booking::class); //Подключим репозиторий броней

        if ($booking_rep->findOneBy(['seat'=>$seat,'flight_id'=>$flight_id,'status'=>'wait'])) { //если уже есть бронь на это место, на этот рейс и не отмененная, то
            return $this->json([ //вернем ошибку
                'status' => 'error',
                'text' => 'Seat occupied',
            ]);
        }


        $email = $this->request->get('email', '');

        if ($email === '') { //если email пустой
            return $this->json([ //вернем ошибку
                'status' => 'error',
                'text' => 'Empty email',
            ]);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { //Если E-Mail невалидный
            return $this->json([ //вернем ошибку
                'status' => 'error',
                'text' => 'Invalid email',
            ]);
        }

        $booking = new \App\Entity\Booking();
        $booking->setEmail($email);
        $booking->setFlight($flight);
        $booking->setSeat($seat);
        $booking->setStatus('wait');
        $this->em->persist($booking);
        $this->em->flush();

        return $this->json([
            'status' => 'success',
            'booking_id' => $booking->getId(), //вернем id брони
            'email' => $booking->getEmail() //вернем email
        ]);
    }

    /**
     * @Route("/CancelBooking", name="booking_cancel")
     */
    public function booking_cancel()
    {
        $booking_id = (int)$this->request->get('booking_id', 0);

        if ($booking_id === 0) { //Если ид рейса 0, то
            return $this->json([ //вернем ошибку
                'status' => 'error',
                'text' => 'Empty booking_id',
            ]);
        }
        $booking_rep = $this->em->getRepository(\App\Entity\Booking::class); //Подключим репозиторий броней
        $booking = $booking_rep->findOneBy(['id'=>$booking_id]);

        if (!$booking) { //Если брони не существует
            return $this->json([ //вернем ошибку
                'status' => 'error',
                'text' => 'Booking not found',
            ]);
        }

        $booking->setStatus('canceled'); //изменяем статус на "отменен"
        //$this->em->remove($booking); если удалять брони
        $this->em->flush();

        return $this->json([ //вернем результат
            'status' => 'success',
            'booking_id' => $booking->getId(),
            'text' => 'Booking canceled'
        ]);
    }

    /**
     * @Route("/BuyBooking", name="booking_buy")
     */
    public function booking_buy()
    {
        $booking_id = (int)$this->request->get('booking_id', 0);

        if ($booking_id === 0) { //Если ид рейса 0, то
            return $this->json([ //вернем ошибку
                'status' => 'error',
                'text' => 'Empty booking_id',
            ]);
        }
        $booking_rep = $this->em->getRepository(\App\Entity\Booking::class); //Подключим репозиторий броней
        $booking = $booking_rep->findOneBy(['id'=>$booking_id]);

        if (!$booking) { //Если брони не существует
            return $this->json([ //вернем ошибку
                'status' => 'error',
                'text' => 'Booking not found',
            ]);
        }

        //сюда можно много чего еще подобавлять по покупке

        $booking->setStatus('buyed'); //изменяем статус на "куплен"
        $this->em->flush();

        return $this->json([ //вернем результат
            'status' => 'success',
            'booking_id' => $booking->getId(),
            'text' => 'Booking buyed'
        ]);
    }

    /**
     * @Route("/BuyedCancelBooking", name="booking_buyed_cancel")
     */
    public function booking_buyed_cancel()
    {
        $booking_id = (int)$this->request->get('booking_id', 0);

        if ($booking_id === 0) { //Если ид рейса 0, то
            return $this->json([ //вернем ошибку
                'status' => 'error',
                'text' => 'Empty booking_id',
            ]);
        }
        $booking_rep = $this->em->getRepository(\App\Entity\Booking::class); //Подключим репозиторий броней
        $booking = $booking_rep->findOneBy(['id'=>$booking_id]);

        if (!$booking) { //Если брони не существует
            return $this->json([ //вернем ошибку
                'status' => 'error',
                'text' => 'Booking not found',
            ]);
        }

        //сюда можно много чего еще подобавлять по возврату покупки

        $booking->setStatus('cancel_buyed'); //изменяем статус на "покупка отменена"
        $this->em->flush();

        return $this->json([ //вернем результат
            'status' => 'success',
            'booking_id' => $booking->getId(),
            'text' => 'Booking buyed cancel'
        ]);
    }
}
