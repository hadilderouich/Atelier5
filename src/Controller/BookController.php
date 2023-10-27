<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\BookType;
use App\Form\RechercheType;
use App\Repository\BookRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;

class BookController extends AbstractController
{
    
    #[Route('/book', name: 'app_book')]
    public function index(): Response
    {
        return $this->render('book/index.html.twig', [
            'controller_name' => 'BookController',
        ]);
    }
   /* //affiche book avant le 2023
   #[Route('/showbook', name: 'showbook')]
    public function showbook(BookRepository $x ,Request $req): Response
    {
        $bookss=$x->findBooks();
        return $this->render('book/showbooktable.html.twig', [
            'books'=> $bookss
        ]);
    }*/
   
    //affiche tableau
    #[Route('/showbooktable', name: 'showbooktable')]
    public function showbooktable(BookRepository $x ,Request $req): Response
    {
        
        $authorName = 'willyamshekspiir';
        $newCategory = 'Romance';
        $x->updateBooks($authorName, $newCategory);

        $Published = $x->PublishedBooks();
        $unPublished = $x->UnpublishedBooks();

        $total= $x->getScienceFictionBooks();

       $startDate = new \DateTime('2014-01-01');
        $endDate = new \DateTime('2018-12-31');
        $between = $x->BetweenDates($startDate, $endDate);

        $tri=$x->trie();   

        $find=$x->findBooks();
        $form=$this->createForm(RechercheType::class);
        $form->handleRequest($req);
        if($form->isSubmitted()){
            $data=$form->get('ref')->getData();
            $Ref=$x->recherBookbyref($data);
            return $this->renderForm('book/showbooktable.html.twig', [
                'book'=> $Ref,
                'f'=> $form,
                'book'=> $tri,
               'book'=>$find,
                'Published'=>$Published,
                'unPublished'=>$unPublished,
                'totalQuantity'=>$total,
                'startDate' => $startDate,
                 'endDate' => $endDate,
                 'books'=>$between
                

            ]);

        }
        return $this->renderForm('book/showbooktable.html.twig', [
            'Published'=>$Published ,
            'unPublished'=>$unPublished,
            'f'=> $form,
            'book'=> $tri,
            'book'=>$find,
            'totalQuantity'=>$total,
            'books'=>$between,
            'startDate' => $startDate,
            'endDate' => $endDate,
            ]); 
     }
//ajout book

        #[Route('/addbook', name: 'addbook')]
        public function addbook(ManagerRegistry $managerRegistry,Request $request): Response
        {
            $em=$managerRegistry->getManager();
            $book=new Book();
            $form=$this->createForm(BookType::class,$book);
            $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em->persist($book);
            $em->flush();
            return $this->redirectToRoute('showbooktable');
         }
            return $this->renderForm('book/addbook.html.twig', [
                'f' => $form,
            ]);
        }   
        //modifier 
        #[Route('/editbook/{ref}', name: 'editbook')]
    public function editbook($ref,BookRepository $bookRepository ,ManagerRegistry $managerRegistry,Request $req): Response
    {
        $em=$managerRegistry->getManager();
        $dataid=$bookRepository->find($ref);
        $form= $this->createForm(BookType::class,$dataid);
        $form->handleRequest($req);
        if($form->isSubmitted() and $form->isValid()){
            $em->persist($dataid);
            $em->flush();
            return $this->redirectToRoute('showbooktable');
        }
        return $this->renderForm('book/editbook.html.twig', [
            'form' => $form 
        ]);
    } 
    //methode delete
    #[Route('/deletbook/{ref}', name: 'deletbook')]
    public function deletbook($ref,BookRepository $bookRepository ,ManagerRegistry $managerRegistry): Response
    {
        $em=$managerRegistry->getManager();
        $ref=$bookRepository->find($ref);
        $em->remove($ref);
        $em->flush();
        return $this->redirectToRoute('showbooktable');
        
    }
}
