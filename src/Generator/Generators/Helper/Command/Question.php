<?php

namespace Generator\Generators\Helper\Command;

use Hurah\Types\Exception\InvalidArgumentException;
use Hurah\Types\Type\IGenericDataType;
use Hurah\Types\Type\PlainText;
use Hurah\Types\Type\TypeType;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class Question {

    private InputInterface $input;
    private OutputInterface $output;

    public function __construct(InputInterface $input, OutputInterface $output) {
        $this->input = $input;
        $this->output = $output;
    }

    private function styleQuestion(string $sRawQuestion, array $aOptions = null, string $sDefault = null): PlainText {
        $sOptionsFormatted = '';
        $sDefaultFormatted = '';

        if (is_iterable($aOptions)) {
            array_walk($aOptions, function (&$option) {
                $option = "<info>$option</info>";
            });
            $sOptionsFormatted = ' (' . join("/", $aOptions) . ')';
        }

        if ($sDefault) {
            $sDefaultFormatted = " [<comment>$sDefault</comment>]";
        }

        return new PlainText("<question>{$sRawQuestion}</question>{$sOptionsFormatted}{$sDefaultFormatted}: ");
    }

    /**
     * Any string / answer is accepted / ok
     * @param string $sQuestion
     * @param TypeType|null $oType a classname that extends IGenericDataType
     * @param string|null $sVarName optional, when passed will be used to check for default values / passed arguments
     * @param string|null $sDefaultValue if a value is already present in getArgument it will get precedence
     * @return IGenericDataType of type $sType
     */
    function ask(string $sQuestion, TypeType $oType = null, string $sVarName = null, string $sDefaultValue = null):
    IGenericDataType {
        while (true) {
            try {
                $sTmpDefaultValue = null;
                if ($sVarName) {
                    $sTmpDefaultValue = $this->input->getArgument($sVarName);
                }

                if ($sTmpDefaultValue) {
                    $sDefaultValue = $sTmpDefaultValue;
                }

                $oContent = $this->styleQuestion($sQuestion, null, $sDefaultValue);

                $oQuestion = new \Symfony\Component\Console\Question\Question($oContent, $sDefaultValue);
                $oHelper = new QuestionHelper();

                $sAnswer = $oHelper->ask($this->input, $this->output, $oQuestion);

                if (empty($sAnswer)) {
                    $sAnswer = $sDefaultValue;
                }

                return $oType->createInstance($sAnswer);
            } catch (InvalidArgumentException $e) {
                $this->output->writeln("<error>{$sQuestion}</error>");
            }
        }
    }

    /**
     * The answer must match with one of the allowed answers.
     *
     * @param string $sQuestion
     * @param TypeType $oType a classname that extends IGenericDataType
     * @param string|null $sVarName optional, when passed will be used to check for default values / passed arguments
     * @param string|null $sDefaultValue = null
     * @param iterable $aOptions = []
     * @return string
     */
    function choose(string $sQuestion, TypeType $oType, string $sVarName = null, string $sDefaultValue = null, iterable $aOptions = []): string {

        if ($sVarName) {
            $sDefaultValue = $this->input->getArgument($sVarName) ?? $sDefaultValue;
        }

        $this->output->writeln($sQuestion);
        $table = new Table($this->output);

        $i = 0;
        $aOptionValues = [];
        $aChoiceValues = [];
        foreach ($aOptions as $oOption)
        {
            ++$i;
            $aOptionValues[] = [$i, "{$oOption}"];
            $aChoiceValues[] = "{$oOption}";
        }


        $table->setHeaders([
                'Id',
                'Value',
            ])->setRows($aOptionValues);
        $table->render();

        $oContent = $this->styleQuestion($sQuestion, null, $sDefaultValue);
        $oQuestion = new ChoiceQuestion($oContent, $aChoiceValues, $sDefaultValue);

        $oHelper = new QuestionHelper();
        $sAnswer = $oHelper->ask($this->input, $this->output, $oQuestion);
        return $sAnswer;
    }

    /**
     * User must answer yes or no
     * @param string $sQuestion
     * @param string|null $sVarName optional, when passed will be used to check for default values / passed arguments
     * @return bool
     */
    function confirm(string $sQuestion, string $sVarName = null, bool $bDefaultValue = false): bool {
        $oContent = $this->styleQuestion($sQuestion, [
            'y',
            'yes',
            'n',
            'no',
        ], $bDefaultValue ? 'y' : 'n');
        $oQuestion = new ConfirmationQuestion($oContent, $bDefaultValue);
        $oHelper = new QuestionHelper();

        $sAnswer = $oHelper->ask($this->input, $this->output, $oQuestion);

        if ($sAnswer) {
            return true;
        }
        return false;
    }
}
