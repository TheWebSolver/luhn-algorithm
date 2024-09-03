<?php
/**
 * Luhn Algoritm test.
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
		$this->assertTrue( ( new LuhnAlgorithm() )( data: 79927398713 ) );
	}

	public function testAsStaticValidate(): void {
		$this->assertTrue( LuhnAlgorithm::validate( value: 79927398713 ) );
	}

	public function testEmptyData(): void {
		$luhn = new LuhnAlgorithm();
		$info = $luhn->__debugInfo();

		$this->assertFalse( $luhn->isValid() );
		$this->assertSame( expected: 0, actual: $luhn->checksum() );
		$this->assertSame( expected: 0, actual: $info['digits'] );
		$this->assertEmpty( $info['state'] );
	}

	public function testUsingInstanceAndInvocable(): void {
		$this->assertTrue( ( new LuhnAlgorithm( 79927398713 ) )() );
		$this->assertTrue( ( new LuhnAlgorithm() )( 79927398713 ) );
		$this->assertTrue( ( new LuhnAlgorithm( 79927398713 ) )( 79927398713 ) );

		// Value passed to constructor and __invoke method must be same.
		$this->expectException( LogicException::class );
		$this->assertTrue( ( new LuhnAlgorithm( 79927398713 ) )( 79927398714 ) );
	}

	public function testNormalizeValue(): void {
		$this->assertSame(
			expected: '123678',
			actual: LuhnAlgorithm::normalize( 'ign@re th!s but not #123 & *678' )
		);
	}
}
