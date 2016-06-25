<?php

namespace JobListingBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity
 * @Table(name="job_listing_company")
 */
class Company {
	/**
	 * @Id @Column(type="integer") @GeneratedValue
	 * @var int
	 */
	private $id;

	/**
	 * @Column(type="string")
	 * @var string
	 */
	private $name;

	/**
	 * @OneToMany(targetEntity="Vacancy", mappedBy="company", indexBy="symbol")
	 * @var Vacancy[]
	 */
	private $vacancies;

	public function __construct( $name ) {
		$this->name      = $name;
		$this->vacancies = new ArrayCollection();
	}

	public function getId() {
		return $this->id;
	}

	public function getName() {
		return $this->name;
	}

	public function addVacancy( Vacancy $vacancy ) {
		$this->vacancies[ $vacancy->getCode() ] = $vacancy;
	}

	public function getVacancy( $code ) {
		if ( ! isset( $this->vacancies[ $code ] ) ) {
			throw new \InvalidArgumentException( "Code is not valid." );
		}

		return $this->vacancies[ $code ];
	}

	public function getVacancies() {
		return $this->vacancies->toArray();
	}
}