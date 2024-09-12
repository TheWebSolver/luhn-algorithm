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
	private const ALLOWED_PATTERN = '/[^0-9]/';
	private const EMPTY           = 'REPRESENTS EMPTY CHECKSUM VALUE @' . self::class;

	private bool $needsDoubling = false;
	private string $digits = '0';
	private int $checksum;
	private mixed $raw;

	/** @var array<int,array{doubled:bool,result:int}> */
	private array $state;

	public function __construct( mixed $value = null ) {
		if ( $value ) {
			$this->runAlgorithm( $value );
		}
	}

	public function __toString(): string {
		$isValid = $this->doValidate( $this->checksum() );

		return ( $isValid ? '' : 'in' ) . "valid [#{$this->digits}] checksum:{$this->checksum}";
	}

	/** @throws LogicException When value mismatch, or neither passed to constructor nor here. */
	public function __invoke( mixed $data = null ): bool {
		return $this->numbers( $data ?? self::EMPTY )->isValid();
	}

	/** @return array{isValid:bool,digits:int,checksum:int,state:array<int,array{doubled:bool,result:int}>} */
	public function __debugInfo(): array {
		return array(
			'isValid'  => $this->doValidate( $this->checksum() ),
			'digits'   => (int) $this->digits,
			'checksum' => $this->checksum,
			'state'    => array_reverse( $this->state ?? array() ),
		);
	}

	public static function validate( mixed $value ): bool {
		return ( new self( $value ) )();
	}

	/** @return int Value sum total. `0` if value can't be casted to string. */
	public function checksum(): int {
		return $this->checksum ??= ( $this->digits ? $this->numbers( $this->raw ?? self::EMPTY )->add() : 0 );
	}

	public function isValid(): bool {
		return $this->doValidate();
	}

	public static function normalize( string $value ): string {
		return preg_replace( self::ALLOWED_PATTERN, replacement: '', subject: $value ) ?? '';
	}

	private function runAlgorithm( mixed $value ): static {
		$this->raw = $value;

		return ! is_scalar( $value ) || ! ( $v = self::normalize( (string) $value ) ) || 2 > ( $l = strlen( $v ) )
			? $this
			: $this->digitsFrom( value: $v, length: $l - 1 );
	}

	private function digitsFrom( string $value, int $length, string $final = '' ): static {
		// Start doubling every next digit from the end of the given value.
		for ( $i = $length; $i >= 0; --$i ) {
			$this->runAlgorithmFor( $value, step: $i, carry: $final );
		}

		$this->digits = strrev( $final );

		return $this;
	}

	private function runAlgorithmFor( string $value, int $step, string &$carry ): void {
		$current = $value[ $step ];
		$doubled = $this->needsDoubling;
		$carry  .= $result = $this->maybeDoubleAndAddDigits( value: (int) $current );

		$this->state[ $step ] = compact( 'doubled', 'result' );
	}

	private function doValidate( ?int $checksum = null ): bool {
		return ( $total = $checksum ?? $this->checksum() ) && ( 0 === $total % 10 );
	}

	private function numbers( mixed $value ): static {
		if ( ! isset( $this->raw ) ) {
			return self::EMPTY !== $value
				? $this->runAlgorithm( $value )
				: throw new LogicException( 'Value not provided for the Luhn Algorithm to create checksum.' );
		}

		// Algorithm already ran from constructor. Responding with calculated digits.
		return ( self::EMPTY === $value || $this->raw === $value )
			? $this
			: throw new LogicException( 'Initialized Luhn Algorithm value does not match with the invoked value.' );
	}

	private function add(): int {
		return (int) array_sum( array: str_split( $this->digits ) );
	}

	private function maybeDoubleAndAddDigits( int $value ): int {
		$double              = $this->needsDoubling;
		$this->needsDoubling = ! $double;

		return ! $double ? $value : ( ( $doubled = $value * 2 ) > 9 ? ( $doubled % 10 ) + 1 : $doubled );
	}
}
