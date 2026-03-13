<?php

namespace OneToMany\LlmSdkBundle\Command;

use OneToMany\LlmSdk\Contract\Enum\Vendor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

use function array_map;

final class ListModelsCommand extends Command
{
    public function __invoke(SymfonyStyle $io): int
    {
        foreach (Vendor::cases() as $vendor) {
            $io->section($vendor->getName());
            $io->listing(array_map(fn ($m): string => $m->getValue(), $vendor->getModels()));
        }

        return Command::SUCCESS;
    }

    /**
     * @see Symfony\Component\Console\Command\Command
     */
    protected function configure(): void
    {
        $this
            ->setName('onetomany:llm-sdk:list-models')
            ->setDescription('Lists all available models by vendor');
    }
}
