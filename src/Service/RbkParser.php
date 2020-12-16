<?php

namespace App\Service;

use App\Entity\Post;
use Symfony\Component\DomCrawler\Crawler;

class RbkParser extends AbstractPostParser
{
    public function getFeedPostsData(string $html): array
    {
        $crawler = new Crawler($html);
        $result = [];

        $nodes = $crawler->filter('.js-news-feed-list > a');
        foreach ($nodes as $node) {
            $node = new Crawler($node);
            $id = $node->attr('id');
            $id = substr(strrchr($id, "_"), 1);
            $href = $node->attr('href');
            $href = false !== strpos($href, '?') ? substr($href, 0, strpos($href, '?')): $href;
            $result[$id] = $href;
        }

        return $result;

    }

    public function downloadPosts(array $postsData): void
    {
        foreach ($postsData as $originalId => $originalUrl) {
            $post = $this->em->getRepository(Post::class)
                ->findOneBy(['originalId' => $originalId]);
            if (null !== $post) {
                continue;
            }
            $html = $this->getHtml($originalUrl);
            $crawler = new Crawler($html);
            $headerElement = $crawler->filter('.article__header__title');
            $contentElements = $crawler->filter('.article__text > p');
            if (0 === $headerElement->count() || 0 === $contentElements->count()) {
                continue;
            }
            $content = '';
            foreach ($contentElements as $element) {
                $node = new Crawler($element);
                $content = $content.'<p>'.$node->text().'</p>';
            }
            $mainImageElement = $crawler->filter('.article__main-image img');
            $authorElement = $crawler->filter('meta[itemprop="author"]');
            $dateElement = $crawler->filter('.article__header__date');

            $post = new Post();
            $post->setOriginalId($originalId);
            $post->setOriginalUrl($originalUrl);
            $post->setHeader($headerElement->text());
            $post->setContent($content);
            $post->setMainImage(0 !== $mainImageElement->count() ? $mainImageElement->attr('src') : null);
            $post->setAuthor(0 !== $authorElement->count() ? $authorElement->attr('content') : null);
            $post->setCreatedAt(0 !== $dateElement->count() ? new \DateTime($dateElement->attr('content')) : null);

            $this->em->persist($post);
        }
        $this->em->flush();
    }
}
