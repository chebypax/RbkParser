<?php

namespace App\Service\Interfaces;

use Doctrine\ORM\EntityManagerInterface;

interface PostParserInterface
{
    public function __construct(EntityManagerInterface $em);

    public function getHtml(string $url): string;
    public function getFeedPostsData(string $html): array;
    public function downloadPosts(array $postsData): void;
}
