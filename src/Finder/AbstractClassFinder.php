<?php

declare(strict_types=1);

namespace Michel\Framework\Core\Finder;

abstract class AbstractClassFinder
{
    protected array $sources = [];
    protected ?string $cacheDir;

    public function __construct(array $sources, ?string $cacheDir = null)
    {
        foreach ($sources as $source) {
            if (!is_dir($source) && !class_exists($source)) {
                throw new \InvalidArgumentException(
                    sprintf('The source "%s" does not exist or is not a directory.', $source)
                );
            }
            $this->sources[] = $source;
        }
        $this->cacheDir = $cacheDir;
        if ($this->cacheDir && !is_dir($this->cacheDir)) {
            throw new \InvalidArgumentException(
                sprintf('Cache directory "%s" does not exist', $this->cacheDir)
            );
        }
    }

    abstract protected function getTargetClassOrInterface(): string;
    
    abstract protected function getCachePrefix(): string;

    protected function findClasses(): array
    {
        $classes = [];
        $target = $this->getTargetClassOrInterface();
        
        foreach ($this->sources as $source) {
            if (class_exists($source, true) && is_subclass_of($source, $target)) {
                $classes[] = $source;
                continue;
            }

            if (is_dir($source)) {
                $classes = array_merge($classes, $this->findClassesInDir($source, $target));
            }
        }

        return array_unique($classes);
    }

    private function findClassesInDir(string $directory, string $target): array
    {
        if ($this->cacheIsEnabled()) {
            $cacheFile = $this->getCacheFile($directory);
            if (is_file($cacheFile)) {
                return require $cacheFile;
            }
        }

        $classes = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
        foreach ($iterator as $file) {
            if ($file->isDir() || $file->getExtension() !== 'php') {
                continue;
            }

            $className = self::extractNamespaceAndClass($file->getPathname());
            if ($className && class_exists($className, true) && is_subclass_of($className, $target)) {
                $classes[] = $className;
            }
        }

        $classes = array_values($classes);
        if ($this->cacheIsEnabled()) {
            $content = "<?php\n\nreturn " . var_export($classes, true) . ";\n";
            file_put_contents($this->getCacheFile($directory), $content);
        }
        return $classes;
    }

    private function cacheIsEnabled(): bool
    {
        return $this->cacheDir !== null;
    }

    private function getCacheFile(string $dir): string
    {
        return rtrim($this->cacheDir, '/') . '/' . md5($this->getCachePrefix() . '_' . $dir) . '.php';
    }

    private static function extractNamespaceAndClass(string $filePath): string
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException('File not found: ' . $filePath);
        }

        $contents = file_get_contents($filePath);
        $namespace = '';
        $class = '';
        $isExtractingNamespace = false;
        $isExtractingClass = false;

        foreach (token_get_all($contents) as $token) {
            if (is_array($token) && $token[0] === T_NAMESPACE) {
                $isExtractingNamespace = true;
            }

            if (is_array($token) && $token[0] === T_CLASS) {
                $isExtractingClass = true;
            }

            if ($isExtractingNamespace) {
                if (is_array($token) && in_array($token[0], [T_STRING, T_NS_SEPARATOR, 265 /* T_NAME_QUALIFIED PHP 8 */])) {
                    $namespace .= $token[1];
                } elseif ($token === ';') {
                    $isExtractingNamespace = false;
                }
            }

            if ($isExtractingClass) {
                if (is_array($token) && $token[0] === T_STRING) {
                    $class = $token[1];
                    break;
                }
            }
        }

        return $namespace ? $namespace . '\\' . $class : $class;
    }
}
