<?php
/**
 * Luhn Algorithm creator.
 *
 * @package TheWebSolver\Codegarage\Validation
 */

 declare( strict_types = 1 );

 namespace TheWebSolver\Codegarage;

use LogicException;

trait Luhn {
	private const ALLOWED_PATTERN  = '/[^0-9]/';
	private const REPRESENTS_EMPTY = 'REPRESENTS EMPTY CHECKSUM VALUE @' . self::class;

	private int $digits = 0;
	private mixed $raw;
	private bool $needsDoubling;

	/** @var array<int,array{doubled:bool,result:int}> */
	private array $state;

	public function __construct( mixed $value = null ) {
		if ( ! $value ) {
			return;
		}

		$this->runAlgorithm( $value );
	}

	public function __toString(): string {
		$isValid = $this->doValidate( $checksum = $this->checksum() );

		return ( $isValid ? '' : 'in' ) . "valid [#{$this->digits}] checksum:{$checksum}";
	}

	/** @throws LogicException When value mismatch, or neither passed to constructor nor here. */
	public function __invoke( mixed $data = null ): bool {
		return $this->canBeInvokedWith( $data ?? self::REPRESENTS_EMPTY )->isValid();
	}

	/** @return array{isValid:bool,digits:int,checksum:int,state:array<int,array{doubled:bool,result:int}>} */
	public function __debugInfo(): array {
		return array(
			'isValid'  => $this->doValidate( $checksum = $this->checksum() ),
			'digits'   => $this->digits,
			'checksum' => $checksum,
			'state'    => array_reverse( $this->state ?? array() ),
		);
	}

	public static function validate( mixed $value ): bool {
		return ( new self( $value ) )();
	}

	/** @return int Value sum total. `0` if value can't be casted to string. */
	public function checksum(): int {
		return $this->digits ? $this->canBeInvokedWith( $this->raw ?? self::REPRESENTS_EMPTY )->add() : 0;
	}

	public function isValid(): bool {
		return $this->doValidate();
	}

	public static function normalize( string $value ): string {
		return preg_replace( self::ALLOWED_PATTERN, replacement: '', subject: $value ) ?? '';
	}

	private function runAlgorithm( mixed $value ): int {
		$this->raw = $value;

		if ( ! is_scalar( $value ) ) {
			return $this->digits = 0;
		}

		if ( ! $value = self::normalize( (string) $value ) ) {
			return $this->digits = 0;
		}

		$valueLength         = strlen( string: $value ) - 1;
		$finalDigits         = '';
		$this->needsDoubling = false;

		// Start without doubling from the end of the given value.
		for ( $i = $valueLength; $i >= 0; --$i ) {
			$this->runAlgorithmFor( $value, step: $i, carry: $finalDigits );
		}

		return $this->digits = (int) strrev( $finalDigits );
	}

	private function runAlgorithmFor( string $value, int $step, string &$carry ): void {
		$current = $value[ $step ];
		$doubled = $this->needsDoubling;
		$carry  .= $result = self::maybeDoubleAndAddDigits( value: (int) $current );

		$this->state[ $step ] = compact( 'doubled', 'result' );
	}

	private function doValidate( ?int $checksum = null ): bool {
		return ( $total = $checksum ?? $this->checksum() ) && 0 === $total % 10;
	}

	private function canBeInvokedWith( mixed $value ): self {
		if ( isset( $this->raw ) ) {
			if ( self::REPRESENTS_EMPTY === $value || $this->raw === $value ) {
				return $this;
			}

			throw new LogicException(
				'Value set during Luhn Algorithm initialization does not match with the invoked value.'
			);
		}

		if ( self::REPRESENTS_EMPTY === $value ) {
			throw new LogicException( 'Value not provided for the Luhn Algorithm to create checksum.' );
		};

		$this->runAlgorithm( $value );

		return $this;
	}

	private function add(): int {
		return (int) array_sum( array: str_split( (string) $this->digits ) );
	}

	private function maybeDoubleAndAddDigits( int $value ): int {
		$digit = $this->needsDoubling
			? ( ( $doubledValue = $value * 2 ) > 9 ? ( $doubledValue % 10 ) + 1 : $doubledValue )
			: $value;

		$this->needsDoubling = ! $this->needsDoubling;

		return $digit;
	}
}
