<?php declare(strict_types=1);

namespace Plugin\jtl_theme_editor;

use Exception;
use JTL\Helpers\Form;
use JTL\Helpers\Text;
use JTL\Plugin\PluginInterface;
use JTL\Shop;
use Less_Parser;
use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\OutputStyle;
use stdClass;
use function Functional\map;

/**
 * Class Editor
 * @package Plugin\jtl_theme_editor
 */
class Editor
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $themesPath;

    /**
     * @var string
     */
    private $parentThemesPath;

    /**
     * @var string
     */
    private $jsPath;

    /**
     * @var string
     */
    private $template;

    /**
     * @var string
     */
    private $parentTemplate;

    /**
     * @var string
     */
    private $userTheme = '';

    /**
     * @var string
     */
    private $userTemplate;

    /**
     * @var stdClass
     */
    private $userData;

    /**
     * @var string
     */
    private $baseTplDir;

    /**
     * Editor constructor.
     * @param PluginInterface $plugin
     */
    public function __construct(PluginInterface $plugin)
    {
        require_once __DIR__ . '/vendor/autoload.php';

        $template             = Shop::Container()->getTemplateService()->getActiveTemplate();
        $this->template       = $template->getDir();
        $this->parentTemplate = $template->getParent();
        $this->path           = $plugin->getPaths()->getAdminPath();
        $this->url            = $plugin->getPaths()->getAdminURL();
        $this->baseTplDir     = \realpath(\PFAD_ROOT . \PFAD_TEMPLATES) . '/';
        $this->themesPath     = \realpath($this->baseTplDir . $this->template . '/themes/');
        $this->jsPath         = \realpath($this->baseTplDir . $this->template . '/js/');
        if ($this->parentTemplate !== null) {
            $this->parentThemesPath = \realpath($this->baseTplDir . $this->parentTemplate . '/themes/');
        }
    }

    /**
     * Shows editor in admin backend.
     */
    public function showEditor(): void
    {
        Shop::Smarty()->assign('URL', $this->url)
            ->assign('themes', $this->getThemes())
            ->display($this->path . '/templates/editor.tpl');
    }

    /**
     * @param null|array $theme
     * @return array
     */
    public function getThemes(?array $theme = null): array
    {
        $themes = [];
        if (\is_dir($this->themesPath) && $handle = \opendir($this->themesPath)) {
            while (($file = \readdir($handle)) !== false) {
                if (($file !== '.' && $file !== '..' && $file !== 'fonts' && $file !== 'base')
                    && ($theme === null || (\is_array($theme) && \in_array($file, $theme, true)))
                ) {
                    $themes[] = [
                        'template' => $this->template,
                        'theme'    => $file,
                    ];
                }
            }
            \closedir($handle);
        }
        if ($this->parentThemesPath !== null
            && \is_dir($this->parentThemesPath)
            && $handle = \opendir($this->parentThemesPath)
        ) {
            while (($file = \readdir($handle)) !== false) {
                if (($file !== '.' && $file !== '..' && $file !== 'fonts' && $file !== 'base')
                    && ($theme === null || (\is_array($theme) && \in_array($file, $theme, true)))
                ) {
                    $themes[] = [
                        'template' => $this->parentTemplate,
                        'theme'    => $file,
                    ];
                }
            }
            \closedir($handle);
        }
        \asort($themes);

        return $themes;
    }

    /**
     * Returns JSON data for called action.
     *
     * @param string $action
     */
    public function json(string $action): void
    {
        if (Form::validateToken()) {
            try {
                $data = $this->call($action);
            } catch (Exception $e) {
                $data = $this->msg('danger', $e->getMessage());
            }
        } else {
            $data = $this->msg('danger', \__('CSRF token missing. Session timeout?'));
        }

        \header('Content-Type: application/json');
        echo \json_encode($data);
    }

    /**
     * @return $this
     */
    private function setUserData(): self
    {
        $this->userTheme    = \basename($_REQUEST['theme'] ?? '');
        $this->userTemplate = \basename($_REQUEST['template'] ?? '');
        if (isset($_REQUEST['data'])) {
            $this->userData          = new stdClass();
            $this->userData->file    = '';
            $this->userData->content = '';
            $this->userData->name    = '';
            if (isset($_REQUEST['data']['file'])) {
                $this->userData->file = \strpos($_REQUEST['data']['file'], $this->baseTplDir) === 0
                    ? \realpath($_REQUEST['data']['file'])
                    : (\realpath($this->path . '/../../../') . '/less/' . \basename($_REQUEST['data']['file']));
            }
            if (isset($_REQUEST['data']['name'])) {
                $this->userData->name = \basename($_REQUEST['data']['name']);
            }
            if (isset($_REQUEST['data']['content'])) {
                $this->userData->content = $_REQUEST['data']['content'];
            }
        }

        return $this;
    }

    /**
     * @param string $action
     * @return array|mixed
     */
    public function call(string $action)
    {
        if (!Shop::isAdmin()) {
            return $this->msg('danger', \__('Unauthorized.'));
        }
        if (\method_exists($this, $action)) {
            return $this->setUserData()->$action();
        }

        return $this->msg('danger', \__('Method not found.'));
    }

    /**
     * @return array
     */
    private function minify(): array
    {
        $out  = $this->jsPath . 'evo.min.js';
        $data = '';
        if (\file_exists($out)) {
            \unlink($out);
        }
        foreach (\glob($this->jsPath . '*.js') as $file) {
            $data .= \file_get_contents($file);
        }
        try {
            $min = Minify_JS_ClosureCompiler::minify($data);
            if (\file_put_contents($out, $min) === false) {
                return $this->msg('danger', \sprintf(\__('File %s could not be saved.'), $out));
            }

            return $this->msg('success', \__('JavaScript compiled successfully.'));
        } catch (\Exception $e) {
            return $this->msg('danger', $e->getMessage());
        }
    }

    /**
     * Read themes current less files.
     *
     * @param string|null $theme
     * @return array|string -  json output
     */
    private function changeTheme(?string $theme = null)
    {
        if ($this->userTheme === '') {
            return '';
        }
        $theme    = $theme ?? $this->userTheme;
        $template = !empty($this->userTemplate) ? $this->userTemplate : null;
        if ($template !== null) {
            $this->themesPath = \realpath($this->baseTplDir . $template . '/themes/') . '/';
        }
        $dir = $this->themesPath . $theme;
        if (\is_writable($dir)) {
            $data    = $this->getLessFiles($theme);
            $files   = $data->files;
            $customs = $data->customs;
            if (empty($files)) {
                $data    = $this->getSassFiles($theme);
                $files   = $data->files;
                $customs = $data->customs;
            }
            \sort($files);

            $return['fn']              = 'showFiles';
            $return['data']['files']   = $files;
            $return['data']['customs'] = $customs;

            return $return;
        }

        return $this->msg('danger', \sprintf(\__('Theme directory %s has no write permissions.'), $dir));
    }

    /**
     * @param string $theme
     * @return stdClass
     */
    private function getSassFiles(string $theme): stdClass
    {
        $res          = new stdClass();
        $res->files   = [];
        $res->customs = [];
        $dir          = $this->themesPath . $theme;
        $themePath    = \realpath($dir . '/sass');
        $basePath     = \realpath($this->themesPath . 'base/sass');
        if ($themePath !== false && ($handle = \opendir($themePath)) !== false) {
            while (($file = \readdir($handle)) !== false) {
                if ($file !== '_assigns.scss'
                    && $file !== 'theme.scss.tmp.scss'
                    && \strpos($file, '.') > 0
                    && \strpos($file, '.scss.original') === false
                ) {
                    $res->files[]   = [
                        'file' => $file,
                        'path' => $dir . '/sass/' . $file,
                    ];
                    $res->customs[] = [
                        'file' => $file,
                        'path' => $dir . '/sass/' . $file,
                    ];
                }
            }
            \closedir($handle);
        }
        if ($basePath !== false && ($handle = \opendir($basePath)) !== false) {
            while (($file = \readdir($handle)) !== false) {
                if ($file !== '_assigns.scss'
                    && $file !== 'theme.scss.tmp.scss'
                    && \strpos($file, '.') > 0
                    && \strpos($file, '.scss.original') === false
                ) {
                    $res->files[] = [
                        'file' => $file,
                        'path' => $this->themesPath . 'base/sass/' . $file,
                    ];
                }
            }
            \closedir($handle);
        }

        return $res;
    }

    /**
     * @param string $theme
     * @return stdClass
     */
    private function getLessFiles(string $theme): stdClass
    {
        $res          = new stdClass();
        $res->files   = [];
        $res->customs = [];
        $dir          = $this->themesPath . $theme;
        $themePath    = \realpath($dir . '/less');
        $basePath     = \realpath($this->themesPath . 'base/less');
        if ($themePath !== false && ($handle = \opendir($themePath)) !== false) {
            while (($file = \readdir($handle)) !== false) {
                if ($file !== '_assigns.less'
                    && $file !== 'theme.less.tmp.less'
                    && \strpos($file, '.') > 0
                    && \strpos($file, '.less.original') === false
                ) {
                    $res->files[]   = [
                        'file' => $file,
                        'path' => $dir . '/less/' . $file,
                    ];
                    $res->customs[] = [
                        'file' => $file,
                        'path' => $dir . '/less/' . $file,
                    ];
                }
            }
            \closedir($handle);
        }
        if ($basePath !== false && ($handle = \opendir($basePath)) !== false) {
            while (($file = \readdir($handle)) !== false) {
                if ($file !== '_assigns.less'
                    && $file !== 'theme.less.tmp.less'
                    && \strpos($file, '.') > 0
                    && \strpos($file, '.less.original') === false
                ) {
                    $res->files[] = [
                        'file' => $file,
                        'path' => $this->themesPath . 'base/less/' . $file,
                    ];
                }
            }
            \closedir($handle);
        }

        return $res;
    }

    /**
     * Saves less/sass file.
     *
     * @return array json output
     */
    private function save(): array
    {
        if (!isset($this->userData->file)) {
            return $this->msg('danger', \__('Input file missing.'));
        }
        $source = $this->userData->file;
        if (!\file_exists($source) || !\in_array(\pathinfo($source)['extension'], ['less', 'css', 'scss'], true)) {
            return $this->msg('danger', \sprintf(\__('File %s could not be saved.'), $source));
        }
        if (isset($this->userData->content)) {
            if (!\file_exists($source . '.original') && !\copy($source, $source . '.original')) {
                return $this->msg(
                    'danger',
                    \sprintf('Backup %s could not be created.', $source . '.original')
                );
            }
            if (!\is_writable($source)
                || \file_put_contents($source, \base64_decode($this->userData->content)) === false
            ) {
                return $this->msg('danger', \sprintf('File %s could not be saved.', $source));
            }

            return $this->msg('success', \__('File saved successfully.'));
        }
        $destination = \realpath($this->themesPath . $this->userTheme . '/less/');
        if ($destination === false) {
            $destination = \realpath($this->themesPath . $this->userTheme . '/sass/');
        }
        if ($destination === false
            || \strpos($destination, $this->baseTplDir) !== 0
            || !\copy($source, $destination . '/' . $this->userData->name)
        ) {
            return $this->msg('danger', \__('Could not copy file (theme directory write permissions?)'));
        }

        return $this->open();
    }

    /**
     * Open less/sass file.
     *
     * @return array json output
     */
    private function open(): array
    {
        $real = \realpath($_REQUEST['data']['file']);
        if (\strpos($real, $this->themesPath) !== 0
            && ($this->parentThemesPath === null || \strpos($real, $this->parentThemesPath) !== 0)
        ) {
            return $this->msg(
                'danger',
                \sprintf(\__('Not allowed to open file %s - %s'), $real, Text::filterXSS($_REQUEST['data']['file']))
            );
        }
        $return                    = [];
        $return['fn']              = 'openFile';
        $return['data']            = [];
        $return['data']['file']    = $_REQUEST['data']['file'];
        $return['data']['name']    = $_REQUEST['data']['name'];
        $return['data']['content'] = \base64_encode(\file_get_contents($real));

        return $return;
    }

    /**
     * Removes less file.
     *
     * @return array json output
     */
    private function reset(): array
    {
        if (empty($this->userData->file)) {
            return $this->msg('danger', \__('File not found.'));
        }
        $file     = $this->userData->file;
        $original = $file . '.original';
        $return   = [];
        if (!\file_exists($original)) {
            return $this->msg('danger', \__('Original not found or file was not edited yet.'));
        }
        if (\unlink($file) === true) {
            \copy($original, $file);
            $return['fn']              = 'enableFile';
            $return['data']['name']    = \basename($file);
            $return['data']['file']    = $file;
            $return['data']['content'] = \base64_encode(\file_get_contents($original));
        } else {
            $return = $this->msg('danger', \__('Could not delete file.'));
        }

        return $return;
    }

    /**
     * @return array
     */
    public function compileFile(): array
    {
        $inputFile = $this->userData->file ?? null;
        if ($inputFile === null) {
            return $this->msg('danger', \sprintf(\__('Invalid input file: %s.'), '-'));
        }
        $input = \realpath($inputFile);
        if ($input && \strpos($inputFile, $this->baseTplDir) === 0) {
            $info      = \pathinfo($input);
            $ext       = $info['extension'];
            $targetDir = $info['dirname'] . '/';
            $dirInfo   = \pathinfo($info['dirname']);
            if ($ext === 'scss') {
                if ($dirInfo['basename'] === 'sass') {
                    $targetDir = $dirInfo['dirname'] . '/';
                }
                $targetFile = $targetDir . $info['filename'] . '.css';
                try {
                    return $this->compileSass($input, $targetFile, $targetDir);
                } catch (Exception $e) {
                    return $this->msg('danger', $e->getMessage());
                }
            }
            if ($ext === 'less') {
                if ($dirInfo['basename'] === 'less') {
                    $targetDir = $dirInfo['dirname'] . '/';
                }
                $targetFile = $targetDir . $info['filename'] . '.css';
                try {
                    return $this->compileLess($input, $targetFile, []);
                } catch (Exception $e) {
                    return $this->msg('danger', $e->getMessage());
                }
            }
        }

        return $this->msg('danger', \sprintf(\__('Invalid input file: %s.'), $input));
    }

    /**
     * Compile theme.
     *
     * @param string|null $theme
     * @param string|null $template
     * @return array - json output
     */
    public function compile(?string $theme = null, ?string $template = null): array
    {
        $cacheDir          = \PFAD_ROOT . \PFAD_COMPILEDIR . 'tpleditortmp';
        $theme             = $theme ?? $this->userTheme;
        $template          = $template ?? $this->userTemplate;
        $directory         = $template === null
            ? $this->themesPath . $theme
            : $this->baseTplDir . $template . '/themes/' . $theme;
        $directory         = \realpath($directory) . '/';
        $sourceMapFilename = 'sourcemap.map';
        $options           = [
            'sourceMap'         => true,
            'sourceMapWriteTo'  => $directory . $sourceMapFilename,
            'sourceMapURL'      => $sourceMapFilename,
            'sourceMapRootpath' => '/',
            'sourceMapBasepath' => \PFAD_ROOT,
        ];
        if (\strpos($directory, $this->baseTplDir) !== 0) {
            return $this->msg('danger', \sprintf(\__('Theme directory %s could not be found.'), $directory));
        }
        if (\defined('THEME_COMPILE_CACHE') && \THEME_COMPILE_CACHE === true) {
            if (\file_exists($cacheDir)) {
                \array_map('\unlink', \glob($cacheDir . '/lessphp*'));
            } else {
                \mkdir($cacheDir, 0777);
            }
            $options['cache_dir'] = $cacheDir;
        }
        $input = $directory . 'less/theme.less';
        if (\file_exists($input)) {
            try {
                return $this->compileLess($input, $directory . 'bootstrap.css', $options);
            } catch (Exception $e) {
                return $this->msg('danger', $e->getMessage());
            }
        }
        $input = $directory . 'sass/' . $theme . '.scss';
        if (\file_exists($input)) {
            try {
                $msg      = $this->compileSass($input, $directory . $theme . '.css', $directory);
                $critical = $directory . 'sass/' . $theme . '_crit.scss';
                if (\file_exists($critical)) {
                    $this->compileSass($critical, $directory . $theme . '_crit.css', $directory);
                }

                return $msg;
            } catch (Exception $e) {
                return $this->msg('danger', $e->getMessage());
            }
        }

        return $this->msg('danger', \sprintf(\__('No less/sass file found in %s for theme %s.'), $directory, $theme));
    }

    /**
     * @param string $file
     * @param string $target
     * @param string $directory
     * @return array
     */
    public function compileSass(string $file, string $target, string $directory): array
    {
        $critical = \strpos($file, '_crit.scss') !== false;
        $baseDir  = $directory . 'sass/';
        $compiler = new Compiler();
        if ($critical === true) {
            $compiler->setOutputStyle(OutputStyle::COMPRESSED);
            $compiler->setSourceMap(Compiler::SOURCE_MAP_NONE);
        } else {
            $compiler->setSourceMap(Compiler::SOURCE_MAP_FILE);
            $compiler->setSourceMapOptions([
                'sourceMapBasepath' => $directory,
                'sourceMapWriteTo'  => $target . '.map',
                'sourceMapURL'      => \basename($target) . '.map'
            ]);
        }
        $compiler->addImportPath($baseDir);
        $compiler->addImportPath(static function ($path) use ($baseDir, $compiler) {
            if (\strpos($path, '.css') === false) {
                $possibleBases         = map($compiler->getParsedFiles(), static function ($i, $e) {
                    return \pathinfo($e)['dirname'] . '/';
                });
                $possibleBases['base'] = $baseDir;
                $possibleBases         = \array_unique(\array_values($possibleBases));
                foreach ($possibleBases as $base) {
                    $real = \realpath($base . $path . '.css');
                    if ($real !== false && \file_exists($real)) {
                        return $real;
                    }
                }
            }

            return null;
        });
        $content = $compiler->compile(\file_get_contents($file));
        $content = \str_replace('content: \'\\\\', 'content: \'\\', $content);
        \file_put_contents($target, $content);

        return $this->msg('success', \sprintf(\__('compileSuccessMsg'), $target));
    }

    /**
     * @param string $file
     * @param string $target
     * @param array  $options
     * @return array
     * @throws \Less_Exception_Parser
     */
    private function compileLess(string $file, string $target, array $options): array
    {
        $parser = new Less_Parser($options);
        $parser->parseFile($file, '/');
        \file_put_contents($target, $parser->getCss());

        return $this->msg('success', \sprintf(\__('compileSuccessMsg'), $target));
    }

    /**
     * Generate a message callback.
     *
     * @param string $type - message class (danger, success, info)
     * @param string $msg - message text
     * @return array json output
     */
    private function msg(string $type, string $msg): array
    {
        return [
            'fn'   => 'message',
            'data' => [
                'type' => $type,
                'msg'  => $msg,
            ],
        ];
    }
}
