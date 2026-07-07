<?php

declare(strict_types=1);

/**
 * Michel PHP Framework
 *
 * @package    MichelFramework
 * @author     Michel.F
 * @license    Mozilla Public License v2.0 (MPL-2.0)
 *
 * Command to install and configure framework packages
 */

namespace Michel\Framework\Core\Command;

use Michel\Console\Command\CommandInterface;
use Michel\Console\InputInterface;
use Michel\Console\OutputInterface;
use Michel\Console\Output\ConsoleOutput;
use Psr\Container\ContainerInterface;

class InstallCommand implements CommandInterface
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getName(): string
    {
        return 'project:init';
    }

    public function getDescription(): string
    {
        return 'Launch the interactive wizard to install and configure optional packages (ORM, Auth).';
    }

    public function getOptions(): array
    {
        return [];
    }

    public function getArguments(): array
    {
        return [];
    }

    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $io = new ConsoleOutput($output);
        $projectDir = $this->container->get('michel.project_dir');

        $configDir = $this->container->get('michel.config_dir');
        $templateDir = $this->container->get('app.template_dir');

        $io->title('Michel Framework Installer 🚀');
        $io->write("Welcome! This wizard will help you customize your skeleton project.\n\n");

        $installOrm = $io->confirm('Do you want to install PaperORM (Database support)?');
        $installAuth = $io->confirm('Do you want to install MichelAuth (Authentication & Security)?');

        $packagesToRequire = [];

        if ($installOrm) {
            $packagesToRequire[] = 'michel/paper-orm:^0.0.4@alpha';

            // Add ORM Package
            $this->appendToConfig($configDir, 'packages.php', 'packages', <<<'PHP'
$packages[\Michel\PaperORM\Michel\Package\MichelPaperORMPackage::class] = ['dev', 'prod'];
PHP
            );

            // Add Database Parameters
            $this->appendToConfig($configDir, 'parameters.php', 'parameters', <<<'PHP'
$parameters['database.host'] = getenv('DATABASE_HOST');
$parameters['database.db'] = getenv('DATABASE_DB');
$parameters['database.user'] = getenv('DATABASE_USER');
$parameters['database.password'] = getenv('DATABASE_PASSWORD');
PHP
            );
        }

        if ($installAuth) {
            $packagesToRequire[] = 'michel/michel-auth:dev-main';

            // Add Auth Package
            $this->appendToConfig($configDir, 'packages.php', 'packages', <<<'PHP'
$packages[\Michel\Auth\MichelPackage\MichelAuthPackage::class] = ['dev', 'prod'];
PHP
            );

            // Add UserProviderInterface service mapping
            $this->appendToConfig($configDir, 'services.php', 'services', <<<'PHP'
$services[\Michel\Auth\UserProviderInterface::class] = static function (\Psr\Container\ContainerInterface $container) {
    return new class($container->get(\Michel\PaperORM\EntityManagerInterface::class)) implements \Michel\Auth\UserProviderInterface {
        use \Michel\Auth\Password\PasswordTrait;
        public function __construct(private \Michel\PaperORM\EntityManagerInterface $em)
        {
        }

        public function findByIdentifier(string $identifier): ?\Michel\Auth\UserInterface
        {
            return $this->em->getRepository(\App\Entity\User::class)
                ->findOneBy(['username' => $identifier])
                ->toObject();
        }

        public function findByToken(string $token): ?\Michel\Auth\UserInterface
        {
            return null;
        }

        public function upgradePassword(\Michel\Auth\PasswordAuthenticatedUserInterface $user, string $newPlainPassword): void
        {
            $user->setPassword($this->hashPassword($newPlainPassword));
            $this->em->persist($user);
            $this->em->flush($user);
        }
    };
};
PHP
            );

            // Ask if they want a Web Security Controller
            $createSecurity = $io->confirm('Do you want to generate a Web Security Controller and Login template? (Say no for API-only apps)');

            if ($createSecurity) {
                // Add Auth parameters
                $this->appendToConfig($configDir, 'parameters.php', 'parameters', <<<'PHP'
$parameters['auth.form.login_path'] = '/login';
$parameters['auth.form.login_key'] = '_username';
$parameters['auth.form.password_key'] = '_password';
PHP
                );

                // Generate SecurityController.php
                $this->generateSecurityController($projectDir);

                // Generate login.html.plate
                $this->generateLoginTemplate($templateDir);
            }

            // Generate User.php Entity
            $this->generateUserEntity($projectDir, $installOrm);
        }

        if (!empty($packagesToRequire)) {
            $io->writeColor("\nInstalling requested packages via Composer...\n", 'yellow');
            $command = 'composer require ' . implode(' ', $packagesToRequire) . ' --ansi';
            passthru($command);
        }

        $io->writeColor("\nInstallation complete! Have fun building with Michel Framework! 🎉\n", 'green');
    }

    /**
     * Appends code statements to a config array variable before it is returned.
     */
    private function appendToConfig(string $configDir, string $filename, string $variableName, string $codeToAppend): void
    {
        $path = $configDir . '/' . $filename;
        if (file_exists($path)) {
            $content = file_get_contents($path);
            $search = "return \${$variableName};";
            if (strpos($content, $codeToAppend) === false) {
                $replace = $codeToAppend . "\n\n" . $search;
                $content = str_replace($search, $replace, $content);
                file_put_contents($path, $content);
            }
        }
    }

    /**
     * Generates User.php Entity class.
     */
    private function generateUserEntity(string $projectDir, bool $hasOrm): void
    {
        $dir = $projectDir . '/src/Entity';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $path = $dir . '/User.php';
        if (file_exists($path)) {
            return;
        }

        if ($hasOrm) {
            $code = <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Entity;

use Michel\Auth\PasswordAuthenticatedUserInterface;
use Michel\Auth\UserInterface;
use Michel\PaperORM\Entity\EntityInterface;
use Michel\PaperORM\Mapping\Column\BoolColumn;
use Michel\PaperORM\Mapping\Column\JsonColumn;
use Michel\PaperORM\Mapping\Column\PrimaryKeyColumn;
use Michel\PaperORM\Mapping\Column\StringColumn;
use Michel\PaperORM\Mapping\Column\TimestampColumn;
use Michel\PaperORM\Mapping\Entity;

#[Entity(table: 'user', repository: null)]
class User implements EntityInterface, UserInterface, PasswordAuthenticatedUserInterface
{
    #[PrimaryKeyColumn]
    private ?int $id = null;

    #[StringColumn(length: 100, unique: true)]
    private ?string $username = null;

    #[StringColumn(length: 150)]
    private ?string $password = null;

    #[JsonColumn(defaultValue: [])]
    private array $roles = [];

    #[BoolColumn(defaultValue: false)]
    private bool $active = false;

    #[TimestampColumn(onCreated: true, nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    #[TimestampColumn(onUpdated: true, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
    }

    public function getPrimaryKeyValue(): ?int
    {
        return $this->getId();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function isAdmin(): bool
    {
        return in_array('ROLE_ADMIN', $this->getRoles());
    }

    public function getUserIdentifier(): string
    {
        return $this->getUsername() ?? '';
    }
}
PHP;
        } else {
            $code = <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Entity;

use Michel\Auth\PasswordAuthenticatedUserInterface;
use Michel\Auth\UserInterface;

class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    private ?int $id = null;
    private ?string $username = null;
    private ?string $password = null;
    private array $roles = [];

    public function __construct()
    {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function isAdmin(): bool
    {
        return in_array('ROLE_ADMIN', $this->getRoles());
    }

    public function getUserIdentifier(): string
    {
        return $this->getUsername() ?? '';
    }
}
PHP;
        }

        file_put_contents($path, $code);
    }

    /**
     * Generates SecurityController.php
     */
    private function generateSecurityController(string $projectDir): void
    {
        $dir = $projectDir . '/src/Controller';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $path = $dir . '/SecurityController.php';
        if (file_exists($path)) {
            return;
        }

        $code = <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Controller;

use Michel\Attribute\Route;
use Michel\Auth\Handler\Authentication\UserFormAuthHandler;
use Michel\Auth\Middlewares\Authentication\UserFormAuthMiddleware;
use Michel\Auth\UserInterface;
use Michel\Framework\Core\Controller\Controller;
use Michel\Framework\Core\Http\Exception\BadRequestException;
use Michel\Session\Storage\SessionStorageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SecurityController extends Controller
{
    public function __construct(private readonly SessionStorageInterface $sessionStorage)
    {
        $this->middleware(UserFormAuthMiddleware::class);
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(ServerRequestInterface $request): ResponseInterface
    {
        $user = $request->getAttribute('user');
        if ($user instanceof UserInterface) {
            return redirect_to('main');
        }

        $error = $this->sessionStorage->get(UserFormAuthHandler::AUTHENTICATION_ERROR);
        $lastUsername = $this->sessionStorage->get(UserFormAuthHandler::LAST_USERNAME);
        $this->sessionStorage->remove(UserFormAuthHandler::AUTHENTICATION_ERROR);
        $this->sessionStorage->remove(UserFormAuthHandler::LAST_USERNAME);

        return render('login.html.plate', [
            'error' => $error,
            'last_username' => $lastUsername
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(ServerRequestInterface $request): ResponseInterface
    {
        throw new BadRequestException('Logout action should be handled by the middleware.');
    }
}
PHP;
        file_put_contents($path, $code);
    }

    /**
     * Generates login.html.plate template
     */
    private function generateLoginTemplate(string $templateDir): void
    {
        if (!is_dir($templateDir)) {
            mkdir($templateDir, 0777, true);
        }

        $path = $templateDir . '/login.html.plate';
        if (file_exists($path)) {
            return;
        }

        $code = <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Log in</title>
</head>
<body>
    <h1>Login</h1>
    <?php if (!empty($error)): ?>
        <div style="color: red;"><?= __e($error) ?></div>
    <?php endif; ?>
    <form action="/login" method="POST">
        <div>
            <label for="username">Username</label>
            <input type="text" id="username" name="_username" value="<?= __e($last_username ?? '') ?>" required autofocus>
        </div>
        <div>
            <label for="password">Password</label>
            <input type="password" id="password" name="_password" required>
        </div>
        <button type="submit">Sign In</button>
    </form>
</body>
</html>
HTML;
        file_put_contents($path, $code);
    }
}
