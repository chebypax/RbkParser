<?php

namespace App\Controller;

use App\Entity\Post;
use App\Service\Interfaces\PostParserInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    const BASE_URL = 'https://www.rbc.ru/';
    /**
     * @var PostParserInterface
     */
    private $parser;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em, PostParserInterface $parser)
    {
        $this->em = $em;
        $this->parser = $parser;
    }

    /**
     * @Route("/", name="default")
     */
    public function indexAction(): Response
    {
        $html = $this->parser->getHtml(self::BASE_URL);
        $postsData = $this->parser->getFeedPostsData($html);
        $this->parser->downloadPosts($postsData);
        $postList = $this->em->getRepository(Post::class)->findBy(['originalId' => array_keys($postsData)]);

        return $this->render('default/index.html.twig', [
            'posts' => $postList,
        ]);
    }

    /**
     * @Route("/{id}", name="post_item")
     * @param Post $post
     *
     * @return Response
     */
    public function itemAction(Post $post): Response
    {
        return $this->render('default/item.html.twig', [
            'post' => $post,
        ]);
    }
}
