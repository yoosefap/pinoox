<?php

namespace pinoox\command;


use pinoox\app\com_pinoox_manager\model\AppModel;
use pinoox\component\console;
use pinoox\component\Dir;
use pinoox\component\File;
use pinoox\component\HelperString;
use pinoox\component\interfaces\CommandInterface;


class modelCreate extends console implements CommandInterface
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "model:create";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Create a model";

    /**
     * The console command Arguments.
     *
     * @var array
     */
    protected $arguments = [
        ['model', true, 'Model Name'],
        ['package_name', false, 'package name of app.'],
    ];

    /**
     * The console command Options.
     *
     * @var array
     */
    protected $options = [
        ['extends', 'e', 'namespace of extends class', 'Model'],
        ["author", "a", "Code author, for copyright in source code.", 'Pinoox'],
        ["link", "l", "Author Connect Link, for copyright in source code.", 'https://www.pinoox.com/'],
        ["license", null, "Put your license in source code (for example:`MIT`).", null],
        ["pinlogo", null, "if write this,pinoox logo into the in source code.", null],
        ["ignoreCopyright", 'i', "Don't show any copyright in source.", null],
        ["migration", 'm', "Create a database migration", true],
    ];

    protected $nameSpaceOfModelFolder = null;
    protected $nameSpaceOfModel = null;
    protected $conteroller = null;
    protected $conterollerPath = null;
    protected $extend = null;
    protected $use = null;
    protected $package = null;

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        $this->chooseApp($this->argument('package_name'));
        $package = $this->cli['package'];
        $this->conterollerPath = Dir::path('~apps/' . $package . '/model');

        $this->nameSpaceOfModelFolder = 'pinoox\app\\' . $package . '\\model';

        $Model = explode('\\', str_replace('/', '\\', $this->argument('model')));

        $this->conteroller = array_pop($Model);
        $ModelScope = implode('\\', $Model);

        $this->nameSpaceOfModel = $this->nameSpaceOfModelFolder . ((count($Model) > 0) ? '\\' . $ModelScope : "");

        $this->conterollerPath = $this->conterollerPath . ((count($Model) > 0) ? '/' . implode('/', $Model) : "") . '/' . ucfirst(strtolower($this->conteroller)) . '.php';

        $extend = str_replace('/', '\\', $this->option('extends'));

        if (HelperString::firstHas(strtolower($extend), 'pinoox\\')) {
            $extend = explode('\\', $extend);
            $this->extend = end($extend);
            $this->use = implode('\\', $extend);
        } elseif ($extend == 'Model') {
            $this->use = 'pinoox\storage\Model';
            $this->extend = 'Model';
        } elseif ($this->option('extends') == null) {
            $this->use = null;
            $this->extend = null;
        } else {
            $extend = explode('\\', $extend);
            $this->extend = end($extend);
            $ModelScope = implode('\\', $Model);
            if ($this->nameSpaceOfModelFolder . ((count($extend) > 1) ? '\\' . $ModelScope : "") != $this->nameSpaceOfModel)
                $this->use = $this->nameSpaceOfModelFolder . ((count($extend) > 1) ? '\\' . $ModelScope : '\\' . $this->extend);
        }

        $isCreated =$this->createModel();
        if (!$isCreated){
            $this->error(sprintf('Can not Create model in "%s"!', str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $this->conterollerPath)));
            $this->newLine();
            exit;
        }

        if (self::hasOption('migration', $this->options)) {
            $this->execute('db:create ' . $this->conteroller);
        }
    }

    private function createModel(): bool
    {
        $code = "<?php \n";
        $code .= $this->createCopyright();
        $code .= $this->createNameSpace();
        if ($this->use != null)
            $code .= sprintf("use %s;\n\n", $this->use);
        $code .= sprintf("class %s ", ucfirst(strtolower($this->conteroller)));
        if ($this->extend != null)
            $code .= sprintf("extends %s\n", $this->extend);
        else
            $code .= "\n";
        $code .= "{\n\n";
        $code .= "}\n";

        return $this->createFile($code);
    }

    private function createCopyright(): string
    {
        if (self::hasOption('ignoreCopyright', $this->options))
            return "";
        $code = "/**\n";
        if (self::hasOption('pinlogo', $this->options)) {
            $code .= " *      ****  *  *     *  ****  ****  *    *\n";
            $code .= " *      *  *  *  * *   *  *  *  *  *   *  *\n";
            $code .= " *      ****  *  *  *  *  *  *  *  *    *\n";
            $code .= " *      *     *  *   * *  *  *  *  *   *  *\n";
            $code .= " *      *     *  *    **  ****  ****  *    *\n";
        } else {
            $code .= " *\n";
        }
        $code .= " *\n";
        $code .= self::hasOption('author', $this->options) ? sprintf(" * @author   %s\n", self::option('author')) : "";
        $code .= self::hasOption('link', $this->options) ? sprintf(" * @link %s\n", self::option('link')) : "";
        $code .= self::hasOption('license', $this->options) ? sprintf(" * @license  %s\n", self::option('license') == 'MIT' ? 'https://opensource.org/licenses/MIT MIT License' : self::option('license')) : "";
        $code .= " */\n\n";
        return $code;
    }

    private function createNameSpace(): string
    {
        return sprintf("namespace %s;\n\n", $this->nameSpaceOfModel);
    }

    private function createFile($content): bool
    {
        if (file_exists($this->conterollerPath)) {
            $this->error(sprintf('Same file exist in "%s"!', str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $this->conterollerPath)));
        }
        if (File::generate($this->conterollerPath, $content)) {
            $this->success(sprintf('Model created in "%s".', str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $this->conterollerPath)));
            $this->newLine();
            return true;
        }
        return false;
    }
}