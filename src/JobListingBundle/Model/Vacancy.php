<?php

namespace JobListingBundle\Model;

/**
 * @Entity
 * @Table(name="job_listing_vacancy")
 */
class Vacancy {
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
	 * @Column(type="string", unique=true)
	 */
	private $code;

	/**
	 * @ManyToOne(targetEntity="Company", inversedBy="company")
	 * @var Vacancy
	 */
	private $company;

	public function __construct( $code, $name, Company $company ) {
		$this->code    = $code;
		$this->name    = $name;
		$this->company = $company;
		$company->addVacancy( $this );
	}

	public function addVacancy( Vacancy $vacancy ) {
		$this->vacancies[ $vacancy->getCode() ] = $vacancy;
	}

	public function getCode() {
		return $this->code;
	}
}