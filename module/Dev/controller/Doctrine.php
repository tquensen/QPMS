<?php

class Dev_Doctrine_Controller extends MiniMVC_Controller
{

    public function runAction($params)
    {
        $_SERVER['argc']--;
        unset($_SERVER['argv'][1]);

        foreach ($_SERVER['argv'] as $k=>$v) {
            if (substr(trim($v), 0, 6) == '--app=' || substr(trim($v), 0, 6) == '--env=') {
                unset($_SERVER['argv'][$k]);
                $_SERVER['argc']--;
            }
        }

        $em = $this->registry->db->em;
        $helpers = array(
            'db' => new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper($em->getConnection()),
            'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($em)
        );

        $cli = new \Symfony\Components\Console\Application('Doctrine Command Line Interface', Doctrine\Common\Version::VERSION);
        $cli->setCatchExceptions(true);
        $helperSet = $cli->getHelperSet();
        foreach ($helpers as $name => $helper) {
            $helperSet->set($helper, $name);
        }
        $cli->addCommands(array(
            // DBAL Commands
            new \Doctrine\DBAL\Tools\Console\Command\RunSqlCommand(),
            new \Doctrine\DBAL\Tools\Console\Command\ImportCommand(),

            // ORM Commands
            new \Doctrine\ORM\Tools\Console\Command\ClearCache\MetadataCommand(),
            new \Doctrine\ORM\Tools\Console\Command\ClearCache\ResultCommand(),
            new \Doctrine\ORM\Tools\Console\Command\ClearCache\QueryCommand(),
            new \Doctrine\ORM\Tools\Console\Command\SchemaTool\CreateCommand(),
            new \Doctrine\ORM\Tools\Console\Command\SchemaTool\UpdateCommand(),
            new \Doctrine\ORM\Tools\Console\Command\SchemaTool\DropCommand(),
            new \Doctrine\ORM\Tools\Console\Command\EnsureProductionSettingsCommand(),
            new \Doctrine\ORM\Tools\Console\Command\ConvertDoctrine1SchemaCommand(),
            new \Doctrine\ORM\Tools\Console\Command\GenerateRepositoriesCommand(),
            new \Doctrine\ORM\Tools\Console\Command\GenerateEntitiesCommand(),
            new \Doctrine\ORM\Tools\Console\Command\GenerateProxiesCommand(),
            new \Doctrine\ORM\Tools\Console\Command\ConvertMappingCommand(),
            new \Doctrine\ORM\Tools\Console\Command\RunDqlCommand(),
            new \Doctrine\ORM\Tools\Console\Command\ValidateSchemaCommand(),

        ));
        $cli->run();
    }
}
