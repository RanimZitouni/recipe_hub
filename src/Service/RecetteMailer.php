<?php
namespace App\Service;

use App\Entity\Recette;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;

class RecetteMailer
{
    public function __construct(private MailerInterface $mailer) {}

    public function sendNouvelleRecetteEmail(Recette $recette, string $destinataire): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('noreply@recipehub.com', 'RecipeHub'))
            ->to($destinataire)
            ->subject('🍽️ Nouvelle recette : ' . $recette->getTitre())
            ->htmlTemplate('emails/nouvelle_recette.html.twig')
            ->context([
                'recette' => $recette,
            ]);

        $this->mailer->send($email);
    }
}