<?php
/**
 *      ****  *  *     *  ****  ****  *    *
 *      *  *  *  * *   *  *  *  *  *   *  *
 *      ****  *  *  *  *  *  *  *  *    *
 *      *     *  *   * *  *  *  *  *   *  *
 *      *     *  *    **  ****  ****  *    *
 * @author   Pinoox
 * @link https://www.pinoox.com/
 * @license  https://opensource.org/licenses/MIT MIT License
 */

namespace pinoox\component\wizard;

class AppWizard extends Wizard implements WizardInterface
{

    public function __construct(string $path, string $filename)
    {
        parent::__construct($path, $filename);
        $this->type('app');
    }

    public function type(string $type)
    {
        $this->type = $type;
    }

    public function install()
    {
        // TODO: Implement install() method.
    }
}