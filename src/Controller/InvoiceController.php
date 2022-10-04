<?php

namespace App\Controller;

use App\Entity\Invoice;
use App\Entity\InvoiceLine;
use App\Form\InvoiceType;
use App\Repository\InvoiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InvoiceController extends AbstractController
{
    #[Route('/', name: 'all_invoices')]
    public function index(InvoiceRepository $invoiceRepository): Response
    {
        $allInvoice = $invoiceRepository->findAll();
        return $this->render('invoice/index.html.twig', [
            'invoices' => $allInvoice
        ]);
    }

    #[Route('/invoice/add', name: 'create_invoice')]
    public function create(Request $request, EntityManagerInterface $manager): Response
    {
        $invoice = new Invoice();

        $form = $this->createForm(InvoiceType::class, $invoice);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            foreach ($invoice->getInvoiceLines() as $line) {
                /** @var InvoiceLine $line*/
                $subtotal = $line->getAmount() * $line->getQuantity();
                $line->setTotal($subtotal + $subtotal*$line->getVat()/100);
            }
            $manager->persist($invoice);
            $manager->flush();

            return $this->redirectToRoute('show_invoice', ['id'  => $invoice->getId()]);
        }

        return $this->render('invoice/create.html.twig', [
            'formInvoice' => $form->createView()
        ]);
    }

    #[Route('/invoice/{id}', name: 'show_invoice')]
    public function show(Invoice $invoice): Response
    {
        return $this->render('invoice/show.html.twig', [
            'invoice' => $invoice
        ]);
    }

}
