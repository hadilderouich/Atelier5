<?php

namespace App\Controller;
use App\Entity\Author;
use App\Form\AuthorType;
use App\Form\MinmaxType;
use App\Repository\AuthorRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;



class AuthorController extends AbstractController
{
    public $authors = array(
        array('id' => 1, 'picture' => '/images/Victor-Hugo.jpg','username' => 'Victor Hugo', 'email' => 'victor.hugo@gmail.com ', 'nb_books' => 100),
        array('id' => 2, 'picture' => '/images/william-shakespeare.jpg','username' => ' William Shakespeare', 'email' =>  ' william.shakespeare@gmail.com', 'nb_books' => 200 ),
        array('id' => 3, 'picture' => '/images/Taha_Hussein.jpg','username' => 'Taha Hussein', 'email' => 'taha.hussein@gmail.com', 'nb_books' => 300),
        );
        //func pricipale
    #[Route('/author', name: 'app_author')]
    public function index(): Response
    {
        return $this->render('author/index.html.twig', [
            'controller_name' => 'AuthorController',
        ]);
    }
    //affiche tableau de author 
    #[Route('/showdbauthor', name: 'showdbauthor')]
    public function showdbauthor(AuthorRepository $x,Request $req): Response
    {
        
        $author = $x->trieAuparEmail();
        $x->deleteAuthors();
        $this->addFlash('success', 'Authors with 0 books deleted.');
        $form=$this->createForm(MinmaxType::class);
        $form->handleRequest($req);
        if($form->isSubmitted() ){
           $min= $form->get('min')->getData();
           $max= $form->get('max')->getData();
           $authors=$x->minmax($min,$max);
           return $this->renderForm('author/showdbauthor.html.twig', [
            'author'=> $authors,
            'f'=> $form
        ]);
        }
        return $this->renderForm('author/showdbauthor.html.twig', [
            'f'=>$form,
            'author'=> $author,

        ]);
            
        }

        //d’ajouter un auteur avec des données statiques sans formulaire.
        #[Route('/addauthor', name: 'addauthor')]
        public function addauthor(ManagerRegistry $managerRegistry): Response
        {
            $em=$managerRegistry->getManager();
            $author =new Author();
            $author->setUsername("3A55");
            $author->setEmail("3A55@gmail.com");
            $em->persist($author);//requette sql   
            $em->flush();
           return  new Response("great add");
           
        }
        #[Route('/addformulaire', name: 'addformulaire')]
       public function addformulaire(ManagerRegistry $managerRegistry,Request $req): Response
    {
        $em=$managerRegistry->getManager();
        $author= new Author();
        $form=$this->createForm(AuthorType::class,$author);
        $form->handleRequest($req);
        if($form->isSubmitted() and $form->isValid()){
            $em->persist($author);
            $em->flush();
            return $this->redirect('showdbauthor');
        }
        return $this->renderForm('author/addformulaire.html.twig', [
            'f'=>$form
        ]);
    }
    #[Route('/editauthor/{id}', name: 'editauthor')]
    public function editauthor($id,AuthorRepository $authorRepository ,ManagerRegistry $managerRegistry,Request $req): Response
    {
        //var_dump($id).die;
        $em=$managerRegistry->getManager();
        $dataid=$authorRepository->find($id);
        //var_dump($dataid).die;
        $form= $this->createForm(AuthorType::class,$dataid);
        $form->handleRequest($req);
        if($form->isSubmitted() and $form->isValid()){
            $em->persist($dataid);
            $em->flush();
            return $this->redirectToRoute('showdbauthor');
        }
        return $this->renderForm('author/editauthor.html.twig', [
            'form' => $form 
        ]);
    }
    //methode delete
    #[Route('/deletauthor/{id}', name: 'deletauthor')]
    public function deletauthor($id,AuthorRepository $authorRepository ,ManagerRegistry $managerRegistry): Response
    {
        $em=$managerRegistry->getManager();
        $id=$authorRepository->find($id);
        $em->remove($id);
        $em->flush();
        return $this->redirectToRoute('showdbauthor');
        
    }
            
}
