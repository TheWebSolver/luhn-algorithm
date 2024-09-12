<?php
/**
 * Luhn Algorithm test.
 *
 * @package TheWebSolver\Codegarage\Test
 */

declare( strict_types = 1 );

use PHPUnit\Framework\TestCase;
use TheWebSolver\Codegarage\LuhnAlgorithm;

class LuhnTest extends TestCase {
	public function testLuhnAlgorithm(): void {
		$luhn = new LuhnAlgorithm( value: 79927398713 );
		$info = $luhn->__debugInfo();

		$this->assertTrue( $luhn->isValid() );
		$this->assertSame( expected: 70, actual: $luhn->checksum() );
		$this->assertSame( expected: 79947697723, actual: $info['digits'] );

		foreach ( str_split( '79947697723' ) as $index => $result ) {
			$this->assertSame( expected: (int) $result, actual: $info['state'][ $index ]['result'] );
		}
	}

	public function testAsInvocable(): void {
		$luhn = new LuhnAlgorithm();

		$this->assertTrue( $luhn( data: 79927398713 ) );
		$this->assertSame( expected: 70, actual: $luhn->checksum() );
	}

	public function testAsStaticValidate(): void {
		$this->assertTrue( LuhnAlgorithm::validate( value: 79927398713 ) );
	}

	public function testExceptionThrownWhenLuhnInitializedWithoutValue(): void {
		$luhn = new LuhnAlgorithm();

		$this->expectExceptionMessage( 'Value not provided for Luhn validation.' );
		$luhn->isValid();
	}

	public function testExceptionThrownWhenLuhnInvokedWithoutValue(): void {
		$luhn = new LuhnAlgorithm();

		$this->expectExceptionMessage( 'Value not provided for Luhn validation.' );
		$luhn();
	}

	public function testUsingInstanceAndInvocable(): void {
		$this->assertTrue( ( new LuhnAlgorithm( 79927398713 ) )() );
		$this->assertTrue( ( new LuhnAlgorithm() )( 79927398713 ) );
		$this->assertTrue( ( new LuhnAlgorithm( 79927398713 ) )( 79927398713 ) );

		$this->expectExceptionMessage(
			'Initialized value does not match with invoked value for Luhn validation.'
		);
		$this->assertTrue( ( new LuhnAlgorithm( 79927398713 ) )( 79927398714 ) );
	}

	public function testNormalizeValue(): void {
		$this->assertSame(
			expected: '123678',
			actual: LuhnAlgorithm::normalize( 'ign@re th!s but not #123 & *678' )
		);
	}
}
