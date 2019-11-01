<?php
namespace develnext\bundle\vdf;

use ide\bundle\AbstractBundle;
use ide\bundle\AbstractJarBundle;
use ide\project\Project;

/**
 * Class VDFBundle
 *
 * @author GIGNIGHT
 * @link gignight.ru / vk.com/gignight1337
 */
class VDFBundle extends AbstractJarBundle
{
    public function onAdd(Project $project, AbstractBundle $owner = null)
    {
        parent::onAdd($project, $owner);
    }

    public function onRemove(Project $project, AbstractBundle $owner = null)
    {
        parent::onRemove($project, $owner);
    }
}