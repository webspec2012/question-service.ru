<?php
namespace App\Service\Question;

use App\Entity\Question\Category;
use App\Exception\ServiceException;
use App\Repository\Question\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Сервис для работы с категориями вопросов
 */
class CategoryService
{
    /**
     * @var CategoryRepository Category Repository
     */
    private CategoryRepository $categoryRepository;

    /**
     * @var EntityManagerInterface Entity Manager
     */
    private EntityManagerInterface $entityManager;

    /**
     * Конструктор сервиса
     *
     * @param CategoryRepository $categoryRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        CategoryRepository $categoryRepository,
        EntityManagerInterface $entityManager
    )
    {
        $this->categoryRepository = $categoryRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @param int $id Идентификатор
     * @return Category Получить категорию по её идентификатору
     * @throws ServiceException В случае если категория не найдена
     */
    public function getCategoryById(int $id): Category
    {
        $category = $this->categoryRepository->findOneById($id);
        if (empty($category)) {
            throw new ServiceException("Не найдена категория с указанным идентификатором");
        }

        return $category;
    }

    /**
     * @param string $slug Slug
     * @return Category Получить категорию по её slug
     * @throws ServiceException В случае если категория не найдена
     */
    public function getCategoryBySlug(string $slug): Category
    {
        $category = $this->categoryRepository->findOneBySlug($slug);
        if (empty($category)) {
            throw new ServiceException("Не найдена категория с указанным идентификатором");
        }

        return $category;
    }

    /**
     * Процесс сохранения категории
     *
     * @param Category $category Category
     * @return Category Сохраненная категория
     */
    public function updateCategory(Category $category): Category
    {
        // действия до сохранению категории
        $category->updatedTimestamps();

        // сохранение категории
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        // действия после сохранения категории

        return $category;
    }
}