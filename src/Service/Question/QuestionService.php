<?php
namespace App\Service\Question;

use App\Entity\Question\Question;
use App\Exception\ServiceException;
use App\Exception\EntityValidationException;
use App\Repository\Question\QuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Dto\Question\QuestionSearchForm;
use App\Dto\Question\QuestionCreateForm;
use App\Pagination\Paginator;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Service\User\UserService;
use App\Service\Question\CategoryService;
use App\Dto\Question\QuestionUpdateForm;

/**
 * Сервис для работы с вопросами и ответами
 */
class QuestionService
{
    /**
     * @var UserService User Service
     */
    private UserService $userService;

    /**
     * @var CategoryService Category Service
     */
    private CategoryService $categoryService;

    /**
     * @var QuestionRepository Question Repository
     */
    private QuestionRepository $questionRepository;

    /**
     * @var EntityManagerInterface Entity Manager
     */
    private EntityManagerInterface $entityManager;

    /**
     * @var SluggerInterface Slugger
     */
    private SluggerInterface $slugger;

    /**
     * Конструктор сервиса
     *
     * @param UserService $userService
     * @param CategoryService $categoryService
     * @param QuestionRepository $questionRepository
     * @param EntityManagerInterface $entityManager
     * @param SluggerInterface $slugger
     */
    public function __construct(
        UserService $userService,
        CategoryService $categoryService,
        QuestionRepository $questionRepository,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    )
    {
        $this->userService = $userService;
        $this->categoryService = $categoryService;
        $this->questionRepository = $questionRepository;
        $this->entityManager = $entityManager;
        $this->slugger = $slugger;
    }

    /**
     * @param int $id Идентификатор вопроса
     * @return Question Получить вопрос по его идентификатору
     * @throws ServiceException В случае если вопрос не найден
     */
    public function getById(int $id): Question
    {
        $question = $this->questionRepository->findOneById($id);
        if (empty($question)) {
            throw new ServiceException("Не найден вопрос с ID '{$id}'");
        }

        return $question;
    }

    /**
     * @param int $categoryId Идентификатор категории
     * @return int Количество вопросов в указанной категории
     */
    public function countQuestionsByCategoryId(int $categoryId): int
    {
        return $this->questionRepository->countQuestionsByCategoryId($categoryId);
    }

    /**
     * Создание вопроса
     *
     * @param QuestionCreateForm $form
     * @return Question Созданный вопрос
     * @throws \App\Exception\AppException
     */
    public function create(QuestionCreateForm $form): Question
    {
        $question = new Question();
        $question->setStatus(Question::STATUS_ACTIVE);
        $question->setUser($this->userService->getUserById($form->userId));
        $question->setCategory($this->categoryService->getById($form->categoryId));
        $question->setTitle($form->title);
        $question->setText((string) $form->text);
        $question->setSlug($this->slugger->slug($form->title));
        $question->setHref('');
        $question->setCreatedByIp((string) $form->createdByIp);

        return $this->save($question);
    }

    /**
     * Обновление вопроса
     *
     * @param int $id Идентификатор вопроса
     * @param QuestionUpdateForm $form
     * @return Question Обновленный вопрос
     * @throws \App\Exception\AppException
     */
    public function update(int $id, QuestionUpdateForm $form): Question
    {
        $question = $this->getById($id);

        $question->setCategory($this->categoryService->getById($form->categoryId));
        $question->setTitle($form->title);
        $question->setText((string) $form->text);
        $question->setSlug($form->slug);

        return $this->save($question);
    }

    /**
     * Листинг вопросов с фильтрацией
     *
     * @param QuestionSearchForm $form Форма поиска
     * @param int $page Номер страницы
     * @param int $pageSize Количество записей на страницу
     * @return Paginator Результат выборка с постраничным выводом
     * @throws \Exception
     */
    public function listing(QuestionSearchForm $form, $page = 1, $pageSize = 30): Paginator
    {
        $query = $this->questionRepository->listingFilter($form);
        return (new Paginator($query, $pageSize))->paginate($page);
    }

    /**
     * Обновить количесто ответов у вопроса
     *
     * @param int $id Идентификатор вопроса
     * @param int $count Количество ответов
     * @return Question Вопрос
     * @throws ServiceException
     */
    public function updateTotalAnswers(int $id, int $count): Question
    {
        $question = $this->getById($id);
        $question->setTotalAnswers($count);

        return $this->save($question);
    }

    /**
     * Удаление вопроса
     *
     * @param int $id Идентификатор вопроса
     * @return Question Удаленный вопрос
     * @throws ServiceException|EntityValidationException
     */
    public function delete(int $id): Question
    {
        $question = $this->getById($id);
        if ($question->isDeleted()) {
            throw new ServiceException("Вопрос уже удален");
        }

        $question->setStatus(Question::STATUS_DELETED);
        return $this->save($question);
    }

    /**
     * Восстановление вопроса
     *
     * @param int $id Идентификатор вопроса
     * @return Question Восстановленный вопрос
     * @throws ServiceException|EntityValidationException
     */
    public function restore(int $id): Question
    {
        $question = $this->getById($id);
        if ($question->isActive()) {
            throw new ServiceException("Вопрос уже активен");
        }

        $question->setStatus(Question::STATUS_ACTIVE);
        return $this->save($question);
    }

    /**
     * Процесс сохранения вопроса
     *
     * @param Question $question Question
     * @return Question Сохраненный вопрос
     */
    private function save(Question $question): Question
    {
        $this->entityManager->persist($question);
        $this->entityManager->flush();

        return $question;
    }
}
