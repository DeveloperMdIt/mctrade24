<?php declare(strict_types=1);

namespace Plugin\jtl_theme_editor;

use JTL\Helpers\Form;
use JTL\Plugin\PluginInterface;
use JTL\Shop;
use Exception;
use Less_Parser;

/**
 * Class Helper
 * @package Plugin\jtl_theme_editor
 */
final class Helper
{
    /**
     * @var PluginInterface
     */
    private $plugin;

    /**
     * @param PluginInterface $plugin
     */
    public function __construct(PluginInterface $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * get options from db
     *
     * @return array
     * @var string $templateDir
     * @var string $theme
     */
    public function getOptions(string $theme = 'evo', string $templateDir = 'Evo'): array
    {
        $result      = [];
        $templateDir = \basename($templateDir);
        $file        = \PFAD_ROOT . \PFAD_TEMPLATES . $templateDir . '/themes/' . $theme . '/less/variables.less';
        if (!\file_exists($file)) {
            return [];
        }
        $handle = \fopen($file, 'rb');
        if ($handle) {
            $vars = [];
            while (($line = \fgets($handle)) !== false) {
                // process the line read.
                if (\strpos($line, ':') !== false) {
                    $x     = \explode(':', $line);
                    $count = \count($x);
                    if ($count === 2) {
                        $x[0]   = \trim($x[0]);
                        $x[1]   = \trim($x[1]);
                        $vars[] = $x;
                    }
                }
            }
            \fclose($handle);
            foreach ($vars as $_var) {
                if (\strpos($_var[0], '@') === 0) {
                    $value   = $_var[1];
                    $type    = 'text';
                    $value   = \str_replace(';', '', $value);
                    $comment = \strpos($value, '//');
                    if ($comment !== false) {
                        $value = \substr($value, 0, $comment);
                    }
                    $value = \trim($value);
                    if ($value[\strlen($value) - 1] === ';') {
                        $value = \substr($value, 0, -1);
                    }

                    $pos = \strpos($value, 'px');
                    $len = \strlen($value);
                    if (\strpos($value, '@') === 0) {
                        $type = 'variable';
                    } elseif (\strpos($value, '#') !== false
                        || ($value === 'transparent'
                            || \strpos($value, 'rgba(') === 0
                            || \strpos($value, 'rgb(') === 0
                        )
                    ) {
                        $type = 'colorpicker';
                    } elseif (\strpos($value, 'darken(') !== false || \strpos($value, 'lighten(') !== false) {
                        $type = 'color';
                    } elseif ($pos === $len - \strlen('px')) {
                        $type = 'px';
                    } elseif (\is_numeric($value) || \strpos($value, '+') !== false || \strpos($value, '*') !== false) {
                        $type = 'number';
                    } elseif (\strpos($value, 'serif') !== false || \strpos($value, 'monospace') !== false) {
                        $type = 'font';
                    }
                    $value = \htmlspecialchars($value);

                    $result[$_var[0]] = [
                        'name'        => \substr($_var[0], 1),
                        'value'       => $value,
                        'type'        => $type,
                        'description' => ''
                    ];
                }
            }

            foreach ($result as $_name => &$_res) {
                if ($_res['type'] === 'variable') {
                    if (isset($result[$_res['value']])) {
                        $_res['type'] = ($result[$_res['value']]['type'] === 'colorpicker')
                            ? 'color'
                            : $result[$_res['value']]['type'];
                    } else {
                        $_res['type'] = 'text';
                    }
                }
            }
            // note: "colorpicker" for _all_ colors - even variables - would be great,
            // but the js colorpicker defaults to rgba(0,0,0,1) for strings like that
        }

        return $result;
    }

    /**
     * save options to db
     *
     * @param array $post
     * @return bool|string
     */
    private function saveOptions(array $post)
    {
        $theme    = isset($post['theme']) ? \basename($post['theme']) : 'evo';
        $themeDir = isset($post['template_dir'])
            ? $post['template_dir'] . 'themes/' . $theme . '/'
            : \PFAD_ROOT . \PFAD_TEMPLATES . 'Evo/themes/' . $theme . '/';
        $file     = \realpath($themeDir) . \DIRECTORY_SEPARATOR . 'less' . \DIRECTORY_SEPARATOR . 'variables.less';
        $original = $file . '.original';
        if (\strpos($file, \PFAD_ROOT . \PFAD_TEMPLATES) !== 0) {
            return false;
        }
        if (!\file_exists($original) && !\copy($file, $original)) {
            return \sprintf($this->plugin->getLocalization()->getTranslation('file_not_writeable'), $file);
        }
        $saveString = '';
        foreach ($post as $postVar => $value) {
            if (\strpos($postVar, 'input-') === 0) {
                $saveString .= ('@' . \substr($postVar, \strlen('input-')) . ': ' . $value . ';' . "\n");
            }
        }

        return \file_put_contents($file, $saveString) > 0
            ? true
            : \sprintf($this->plugin->getLocalization()->getTranslation('file_not_writeable'), $file);
    }

    /**
     * POST handler for back and frontend
     *
     * @param array $post
     * @return array
     */
    public function handlePost(array $post): array
    {
        $result = [
            'ok'  => false,
            'msg' => utf8_encode($this->plugin->getLocalization()->getTranslation('msg_invalid_token'))
        ];

        if (true !== Form::validateToken()) {
            return $result;
        }
        if (!Shop::isAdmin()) {
            $result['msg'] = $this->plugin->getLocalization()->getTranslation('msg_no_admin_user');

            return $result;
        }
        $result = ['ok' => false, 'msg' => $this->plugin->getLocalization()->getTranslation('compile_failed')];
        if (!isset($post['template_dir'], $post['theme'])) {
            return $result;
        }
        $theme       = isset($post['theme']) ? \basename($post['theme']) : 'evo';
        $templateDir = isset($post['template_dir'])
            ? \realpath($post['template_dir'] . 'themes/' . $theme) . \DIRECTORY_SEPARATOR
            : \realpath(\PFAD_ROOT . \PFAD_TEMPLATES . 'Evo/themes/' . $theme) . \DIRECTORY_SEPARATOR;
        if (\strpos($templateDir, \realpath(\PFAD_ROOT . \PFAD_TEMPLATES)) !== 0) {
            return $result;
        }
        $variablesLess = $templateDir . 'less/variables.less';
        $themeLess     = $templateDir . 'less/theme.less';
        $compiledCSS   = $templateDir . 'bootstrap.css';
        if (!\file_exists($variablesLess) || !\is_writable($variablesLess)) {
            $result['msg'] = \sprintf(
                $this->plugin->getLocalization()->getTranslation('file_not_writeable'),
                $variablesLess
            );

            return $result;
        }
        if (!\file_exists($themeLess)) {
            $result['msg'] = \sprintf(
                $this->plugin->getLocalization()->getTranslation('file_doest_not_exist'),
                $themeLess
            );

            return $result;
        }
        if (!\file_exists($compiledCSS) || !\is_writable($compiledCSS)) {
            $result['msg'] = \sprintf(
                $this->plugin->getLocalization()->getTranslation('file_not_writeable'),
                $compiledCSS
            );

            return $result;
        }

        $save = $this->saveOptions($post);
        if ($save !== true) {
            $result['msg'] = $save;

            return $result;
        }

        require_once __DIR__ . '/vendor/autoload.php';
        try {
            $parser = new Less_Parser();
            $parser->parseFile($themeLess, '/');
            $css           = $parser->getCss();
            $write         = \file_put_contents($compiledCSS, $css);
            $result['ok']  = ($write > 0);
            $result['msg'] = \vsprintf(
                $this->plugin->getLocalization()->getTranslation('compile_ok'),
                [$theme, $compiledCSS]
            );
        } catch (Exception $e) {
            $result['msg'] = $this->plugin->getLocalization()->getTranslation('compile_failed') . $e->getMessage();
        }

        return $result;
    }
}
