<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

use PHPStan\Type\ErrorType;
use PHPStan\Type\Generic\TemplateTypeMap;
use PHPStan\Type\Type;
use function array_merge;

class GenericParametersAcceptorResolver
{

	/**
	 * @api
	 * @param Type[] $argTypes
	 */
	public static function resolve(array $argTypes, ParametersAcceptor $parametersAcceptor): ParametersAcceptor
	{
		$typeMap = TemplateTypeMap::createEmpty();

		foreach ($parametersAcceptor->getParameters() as $i => $param) {
			if (isset($argTypes[$i])) {
				$argType = $argTypes[$i];
			} elseif ($param->getDefaultValue() !== null) {
				$argType = $param->getDefaultValue();
			} else {
				break;
			}

			$paramType = $param->getType();
			$typeMap = $typeMap->union($paramType->inferTemplateTypes($argType));
		}

		return new ResolvedFunctionVariant(
			$parametersAcceptor,
			new TemplateTypeMap(array_merge(
				$parametersAcceptor->getTemplateTypeMap()->map(static fn (string $name, Type $type): Type => new ErrorType())->getTypes(),
				$typeMap->getTypes(),
			)),
		);
	}

}
