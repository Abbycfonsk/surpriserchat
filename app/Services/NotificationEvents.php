<?php

namespace App\Services;

use App\Helpers\Notify;
use App\Models\Offer;
use App\Models\Review;
use App\Models\Surprise;
use App\Models\User;
use App\Models\Skill;

class NotificationEvents
{
    /* ---------------------------------------------------------
     *  SURPRISE EVENTS
     * --------------------------------------------------------- */

    public static function surpriseCreated(Surprise $surprise)
    {
        Notify::success(
            $surprise->creator_id,
            'Sorpresa creada',
            'Tu sorpresa ha sido creada correctamente.',
            ['surprise_id' => $surprise->id]
        );
    }

    public static function surpriseStarted(Surprise $surprise)
    {
        if ($surprise->creator_id) {
            Notify::info(
                $surprise->creator_id,
                'Sorpresa iniciada',
                'El genius ha comenzado a trabajar en tu sorpresa.',
                ['surprise_id' => $surprise->id]
            );
        }
    }

    public static function surpriseDelivered(Surprise $surprise)
    {
        Notify::success(
            $surprise->creator_id,
            'Sorpresa entregada',
            'El genius ha entregado tu sorpresa.',
            ['surprise_id' => $surprise->id]
        );
    }

    public static function surpriseCompleted(Surprise $surprise)
    {
        // Notificar al creador
        Notify::success(
            $surprise->creator_id,
            'Sorpresa completada',
            'Has marcado la sorpresa como completada.',
            ['surprise_id' => $surprise->id]
        );

        // Notificar al genius
        if ($surprise->genius_id) {
            Notify::success(
                $surprise->genius_id,
                'Sorpresa completada',
                'El creador ha completado la sorpresa.',
                ['surprise_id' => $surprise->id]
            );
        }
    }

    public static function surpriseCancelled(Surprise $surprise)
    {
        if ($surprise->genius_id) {
            Notify::info(
                $surprise->genius_id,
                'Sorpresa cancelada',
                'El creador ha cancelado la sorpresa.',
                ['surprise_id' => $surprise->id]
            );
        }
    }

    public static function deadlineUpdated(Surprise $surprise)
    {
        if (!$surprise->genius_id) return;

        Notify::info(
            $surprise->genius_id,
            'Deadline actualizado',
            'El creador ha actualizado la fecha límite de la sorpresa.',
            ['surprise_id' => $surprise->id]
        );
    }

    /* ---------------------------------------------------------
     *  OFFER EVENTS
     * --------------------------------------------------------- */

    public static function offerReceived(Offer $offer)
    {
        $surprise = $offer->surprise;

        Notify::info(
            $surprise->creator_id,
            'Nueva oferta recibida',
            'Un genius ha enviado una oferta para tu sorpresa.',
            [
                'surprise_id' => $surprise->id,
                'offer_id'    => $offer->id,
            ]
        );
    }

    public static function offerAccepted(Offer $offer)
    {
        $surprise = $offer->surprise;

        Notify::success(
            $offer->genius_id,
            'Oferta aceptada',
            'Tu oferta ha sido aceptada. ¡Empieza la sorpresa!',
            [
                'surprise_id' => $surprise->id,
                'offer_id'    => $offer->id,
            ]
        );
    }

    public static function offerRejected(Offer $offer)
    {
        $surprise = $offer->surprise;

        Notify::info(
            $offer->genius_id,
            'Oferta rechazada',
            'La sorpresa ha sido asignada a otro genius.',
            [
                'surprise_id' => $surprise->id,
                'offer_id'    => $offer->id,
            ]
        );
    }

    /* ---------------------------------------------------------
     *  FILE EVENTS
     * --------------------------------------------------------- */

    public static function fileUploadedByCreator(Surprise $surprise)
    {
        if (!$surprise->genius_id) return;

        Notify::info(
            $surprise->genius_id,
            'Nuevo archivo del creador',
            'El creador ha subido un archivo a la sorpresa.',
            ['surprise_id' => $surprise->id]
        );
    }

    public static function fileUploadedByGenius(Surprise $surprise)
    {
        Notify::info(
            $surprise->creator_id,
            'Nuevo archivo disponible',
            'El genius ha subido un archivo a tu sorpresa.',
            ['surprise_id' => $surprise->id]
        );
    }
    public static function messageSent(\App\Models\Conversation $conversation, \App\Models\Message $message)
    {
        $surprise = $conversation->surprise;
        $senderId = $message->sender_id;

        // Determinar receptor
        if ($senderId === $conversation->creator_id) {
            $receiverId = $conversation->genius_id;
            $title = 'Nuevo mensaje del creador';
            $body = 'Has recibido un nuevo mensaje en la sorpresa.';
        } else {
            $receiverId = $conversation->creator_id;
            $title = 'Nuevo mensaje del genius';
            $body = 'Has recibido un nuevo mensaje en la sorpresa.';
        }

        \App\Helpers\Notify::info(
            $receiverId,
            $title,
            $body,
            [
                'surprise_id' => $surprise->id,
                'conversation_id' => $conversation->id,
                'message_id' => $message->id,
            ]
        );
    }

    /* ---------------------------------------------------------
     *  REVIEW EVENTS
     * --------------------------------------------------------- */

    public static function reviewCreated(Review $review)
    {
        Notify::success(
            $review->reviewed_user_id,
            'Nueva reseña recibida',
            'Has recibido una nueva reseña.',
            [
                'surprise_id'       => $review->surprise_id,
                'review_id'         => $review->id,
                'rating_genius'     => $review->rating_genius,
                'rating_surprise'   => $review->rating_surprise,
            ]
        );
    }
    public static function offerCountered(Offer $offer, \App\Models\OfferBid $bid)
    {
        $surprise = $offer->surprise;

        // Si el que contraoferta es el genius, notificamos al creador
        if ($bid->role === 'genius') {
            Notify::info(
                $surprise->creator_id,
                'Nueva contraoferta del genius',
                'El genius ha enviado una contraoferta.',
                [
                    'surprise_id' => $surprise->id,
                    'offer_id'    => $offer->id,
                    'bid_id'      => $bid->id,
                ]
            );
        } else {
            // Si es el creador, notificamos al genius
            Notify::info(
                $offer->genius_id,
                'Nueva contraoferta del creador',
                'El creador ha enviado una contraoferta.',
                [
                    'surprise_id' => $surprise->id,
                    'offer_id'    => $offer->id,
                    'bid_id'      => $bid->id,
                ]
            );
        }
    }

    /* ---------------------------------------------------------
     *  SKILL EVENTS
     * --------------------------------------------------------- */

    public static function skillLevelUp(User $user, Skill $skill, int $newLevel)
    {
        Notify::success(
            $user->id,
            '¡Has subido de nivel!',
            'Has alcanzado el nivel ' . $newLevel . ' en la skill ' . $skill->name,
            [
                'skill_id'  => $skill->id,
                'new_level' => $newLevel,
            ]
        );
    }
}
