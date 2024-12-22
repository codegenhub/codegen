<?php

namespace Codegenhub\App\Commands;

use OpenAI;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Test extends Command
{
    public function getDescription(): string
    {
        return '';
    }

    public function getName()
    {
        return 'gpt:test';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $apiKey = trim(file_get_contents(dirname(__FILE__) . '/../../gpt_key.txt'));

        $client = OpenAI::client($apiKey);

        $result = $client->completions()->create([
            'model' => 'text-davinci-003',
            'prompt' => 'Как выбрать тему проекта?',
        ]);

        echo $result['choices'][0]['text'];

        return 0;
    }
}
