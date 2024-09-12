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

	/** @throws LogicException When initialized without value. */
	public function __toString(): string {
		return ( $this->isValid() ? '' : 'in' ) . "valid [#{$this->digits}] checksum:{$this->checksum}";
	}

	/** @throws LogicException When initialized without value or constructor value & invoked value mismatch. */
	public function __invoke( mixed $data = null ): bool {
		return $this->ensureNumber( $data ?? self::EMPTY )->isValid();
	}

	/**
	 * @return array{isValid:bool,digits:int,checksum:int,state:array<int,array{doubled:bool,result:int}>}
	 * @throws LogicException When initialized without value.
	 */
	public function __debugInfo(): array {
		return array(
			'isValid'  => $this->isValid(),
			'digits'   => (int) $this->digits,
			'checksum' => $this->checksum(),
			'state'    => array_reverse( $this->state ?? array() ),
		);
	}

	/** @throws LogicException When initialized with empty value. */
	public static function validate( mixed $value ): bool {
		return ( new self( $value ) )();
	}

	/** @throws LogicException When initialized without value. */
	public function checksum(): int {
		return $this->checksum ??= $this->ensureNumber( $this->raw ?? self::EMPTY )->add();
	}

	/** @throws LogicException When initialized without value. */
	public function isValid(): bool {
		return 0 === $this->checksum() % 10;
	}

	public static function normalize( string $value ): string {
		return preg_replace( self::ALLOWED_PATTERN, replacement: '', subject: $value ) ?? '';
	}

	private function runAlgorithm( mixed $value ): static {
		$this->raw = $value;

		return ! is_scalar( $value ) || ! ( $v = self::normalize( (string) $value ) ) || 2 > ( $l = strlen( $v ) )
			? $this
			: $this->computeDigitsFrom( value: $v, length: $l - 1 );
	}

	private function computeDigitsFrom( string $value, int $length, string $final = '' ): static {
		// Start doubling every second digit from the end of the given value.
		for ( $i = $length; $i >= 0; --$i ) {
			$this->runAlgorithmFor( $value, step: $i, carry: $final );
		}

		$this->digits = strrev( $final );

		return $this;
	}

	private function runAlgorithmFor( string $value, int $step, string &$carry ): void {
		$current              = $value[ $step ];
		$doubled              = $this->needsDoubling;
		$carry               .= $result = $this->maybeDoubleAndAddDigits( value: (int) $current );
		$this->state[ $step ] = compact( 'doubled', 'result' );
	}

	private function add(): int {
		return (int) array_sum( array: str_split( $this->digits ) );
	}

	private function maybeDoubleAndAddDigits( int $value ): int {
		$double              = $this->needsDoubling;
		$this->needsDoubling = ! $double;

		return ! $double ? $value : ( ( $doubled = $value * 2 ) > 9 ? ( $doubled % 10 ) + 1 : $doubled );
	}

	private function ensureNumber( mixed $value ): static {
		if ( ! isset( $this->raw ) ) {
			return self::EMPTY !== $value ? $this->runAlgorithm( $value ) : $this->throw( hasValue: false );
		}

		return ( self::EMPTY === $value || $this->raw === $value ) ? $this : $this->throw( hasValue: true );
	}

	private function throw( bool $hasValue ): never {
		$valueError = $hasValue ? 'Initialized value does not match with invoked value' : 'Value not provided';

		throw new LogicException( $valueError . ' for Luhn validation.' );
	}
}
