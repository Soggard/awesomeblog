<?php

namespace App\DataFixtures;

use App\Entity\Articles;
use App\Entity\Author;
use App\Entity\Category;
use App\Entity\Comment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $createdCategories = [];
        $createdAuthors = [];
        $createdArticles = [];

        // Fill Category table
        $categories = [
            "Comfort Zone",
            "Little prince",
            "Alabama",
            "Strawberry-Banana-Kiwi",
            "Age of Empire 4",
            "Fluid mecanics"
        ];
        foreach ($categories as $category) {
            $newCategory = new Category();
            $newCategory->setName($category);
            $manager->persist($newCategory);
            $createdCategories[] = $newCategory;
        }

        // Fill Author table
        $authors = [
            "Philippe",
            "Patrique",
            "Simon",
            "Jean-Paul 2",
            "Spongebob"
        ];
        foreach ($authors as $author) {
            $newAuthor = new Author();
            $newAuthor->setName($author);
            $manager->persist($newAuthor);
            $createdAuthors[] = $newAuthor;
        }

        // Fill Article table
        for ($i = 0; $i < 10; $i++) {
            $newArticle = new Articles();
            $newArticle->setTitle("Article " . $i);
            $newArticle->setText("Lorem ispum edmut get de flamenverfen.");
            $newArticle->setAuthor($createdAuthors[rand(0, count($createdAuthors) - 1)]);
            $newArticle->setCategory($createdCategories[rand(0, count($createdCategories) - 1)]);
            $manager->persist($newArticle);
            $createdArticles[] = $newArticle;
        }

        // Fill Comment table
        for ($i = 0; $i < 50; $i++) {
            $newComment = new Comment();
            $newComment->setText("Lorem ispum edmut get de flamenverfen." . $i);
            $newComment->setArticle($createdArticles[rand(0, count($createdArticles) - 1)]);
            $manager->persist($newComment);
        }
        $manager->flush();
    }
}
