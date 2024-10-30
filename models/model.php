<?php

/**
 * Model.
 * 
 * @since 1.0
 * 
 * @author Local Biz Commando
 */
class LBCLocalSEOModel extends LBCLocalSEOCommon
{
	private $_default_settings_form_values = array(
		'addr_format'			=> '',
		'location_page_slug'	=> 'location',
		'google_maps_api_key'	=> '',
		'opening_hours_format'	=> 24
	);
	
	private $_default_location_settings_form_values = array(
		'name'									=> '',
		'meta_desc'								=> '',
		'type'									=> 'LocalBusiness',
		'short_desc'							=> '',
		'url'									=> '',
		'logo'									=> '',
		'street_address'						=> '',
		'city'									=> '',
		'state'									=> '',
		'country'								=> '',
		'postcode'								=> '',
		'on_location_page_map'					=> 1,
		'latitude'								=> '',
		'longitude'								=> '',
		'on_location_page_contacts'				=> 1,
		'phone_numbers'							=> array(),
		'fax_numbers'							=> array(),
		'email'									=> '',
		'on_location_page_payments'				=> 0,
		'accepted_payment_type'					=> '',
		'accepted_currencies'					=> '',
		'price_range'							=> '',
		'on_location_page_opening_hours'		=> 0,
		'opening_hours'							=> array(),
		'on_location_page_spec_opening_hours'	=> 0,
		'spec_opening_hours'					=> array(),
		'on_location_page_social_links'			=> 0,
		'save_date'								=> ''
	);
	
	private $_days = array();
	
	private $_slug_regexp = '/^[a-z0-9]+(-[a-z0-9]+)*$/';
	private $_coordinate_regexp = '/^[\-]{0,1}[0-9]+(\.[0-9]+){0,1}$/';
	private $_time_format_24_regexp = '/^([0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/';
	private $_time_format_12_regexp = '/^([1-9]|1[0-2]):[0-5][0-9](am|pm)$/i';
	
	/**
	 * Constructor.
	 *
	 * @since 1.0
	 */
	public function __construct()
	{
		$addr_formats = array_keys($this->get_address_formats());
		if (isset($addr_formats[0])) {
			$this->_default_settings_form_values['addr_format'] = $addr_formats[0];
		}
		
		$this->_days = array(
			'mon'	=> __('Monday', 'lbc-local-seo'),
			'tue'	=> __('Tuesday', 'lbc-local-seo'),
			'wed'	=> __('Wednesday', 'lbc-local-seo'),
			'thu'	=> __('Thursday', 'lbc-local-seo'),
			'fri'	=> __('Friday', 'lbc-local-seo'),
			'sat'	=> __('Saturday', 'lbc-local-seo'),
			'sun'	=> __('Sunday', 'lbc-local-seo')
		);
		
		$social_icons = $this->get_social_icons();
		foreach ($social_icons as $si_id => $si_data) {
			$this->_default_location_settings_form_values['social_link_' . $si_id] = '';
		}
	}
	
	/**
	 * Returns if the given slug exists among the top level admin menus.
	 * 
	 * @param string $slug
	 * 
	 * @since 1.0
	 * 
	 * @return boolean True if it exists, otherwise false.
	 */
	public function does_top_level_admin_menu_exist($slug)
	{
		global $menu;
		
		$ret = false;
		foreach($menu as $item) {
			if (isset($item[2]) && $item[2] == $slug) {
				$ret = true;
				break;
			}
		}
		
		return $ret;
	}
	
	/**
	 * Returns the content of the given view.
	 * Purpose: loading a view for FE.
	 * 
	 * @param string $view_name The name of the view. The format is restricted, check the code.
	 * @param array $view_data Variables/data for the view.
	 * 
	 * @since 1.0
	 * 
	 * @return string Content of the view (HTML code).
	 */
	public function get_view($view_name, $view_data = false)
	{
		if (preg_match('/^[a-z0-9_\-]+$/i', $view_name) != 1) {
			return __('Invalid view name.', 'lbc-local-seo');
		}
		
		ob_start();
		
		include($this->_get_plg_root_path() . 'views/' . $view_name . '.php');
		
		return ob_get_clean();
	}
	
	/**
	 * Creates the location page if it does not exist yet.
	 * If the slug has been modified by WP then the location_page_slug option will be modified as well.
	 * E.g.:
	 *   the user sets 'location' as slug, the WP modifies it to 'location-2' then
	 *   the location_page_slug option is updated to 'location-2'
	 * 
	 * @since 1.0
	 */
	public function add_location_page()
	{
		if (($settings = get_option('lbc_local_seo_settings')) !== false &&
			isset($settings['location_page_slug']) &&
			$settings['location_page_slug'] != '')
		{
			$slug = $settings['location_page_slug'];
		}
		else {
			$slug = $this->_default_settings_form_values['location_page_slug'];
		}
		
		$page = get_posts(array(
			'pagename'	=> $slug,
			'post_type'	=> 'page'
		));
		if (!$page) {
			$post = array(
				'post_name'			=> $slug,
				'post_title'		=> __('Location', 'lbc-local-seo'),
				'post_content'		=> '',
				'post_status'		=> 'publish',
				'post_type'			=> 'page',
				'comment_status' 	=> 'closed',
				'ping_status'		=> 'closed'
			);
			
			$page_id = wp_insert_post($post);
			
			$page2 = get_posts(array(
				'ID'		=> $page_id,
				'post_type'	=> 'page'
			));
			if ($page2) {
				if ($settings['location_page_slug'] != $page2[0]->post_name) {
					$settings['location_page_slug'] = $page2[0]->post_name;
					
					update_option('lbc_local_seo_settings', $settings);
				}
			}
		}
	}
	
	/**
	 * Returns address formats.
	 * 
	 * @since 1.0
	 * 
	 * @return array Address formats.
	 */
	public function get_address_formats()
	{
		/**
		 * {street address}
		 * {region} = {state}
		 * {city}
		 * {postcode} = {zipcode}
		 * {country}
		 */
		$street_addr = '{' . __('street address', 'lbc-local-seo') . '}';
		$region = '{' . __('region', 'lbc-local-seo') . '}';
		$state = '{' . __('state', 'lbc-local-seo') . '}';
		$city = '{' . __('city', 'lbc-local-seo') . '}';
		$postcode = '{' . __('postcode', 'lbc-local-seo') . '}';
		$zipcode = '{' . __('zipcode', 'lbc-local-seo') . '}';
		$country = '{' . __('country', 'lbc-local-seo') . '}';
		
		return array(
			'{street address} / {city}, {state} {zipcode} / {country}'		=> $street_addr . ' / ' . $city . ', ' . $state . ' ' . $zipcode . ' / ' . $country,
			'{street address} / {region} / {city} / {postcode} / {country}'	=> $street_addr . ' / ' . $region . ' / ' . $city . ' / ' . $postcode . ' / ' . $country,
			'{street address} / {postcode} {city} / {country}'				=> $street_addr . ' / ' . $postcode . ' ' . $city . ' / ' . $country,
			'{postcode} / {city} / {street address} / {country}'			=> $postcode . ' / ' . $city . ' / ' . $street_addr . ' / ' . $country
		);
	}
	
	/**
	 * Returns opening hours formats. 12/24 hour-format.
	 * 
	 * @since 1.0
	 * 
	 * @return array
	 */
	public function get_opening_hours_formats()
	{
		return array(
			24	=> '24 ' . __('hours format', 'lbc-local-seo'),
			12	=> '12 ' . __('hours format', 'lbc-local-seo')
		);
	}
	
	/**
	 * Returns business types.
	 * 
	 * @since 1.0
	 * 
	 * @return array
	 */
	public function get_business_types()
	{
		return array(
			'AnimalShelter'	=> array(
				'title'		=> __('Animal Shelter', 'lbc-local-seo')
			),
			'AutomotiveBusiness'	=> array(
				'title'		=> __('Automotive Business', 'lbc-local-seo'),
				'subtypes'	=> array(
					'AutoBodyShop'		=> __('Auto Body Shop', 'lbc-local-seo'),
					'AutoDealer'		=> __('Auto Dealer', 'lbc-local-seo'),
					'AutoPartsStore'	=> __('Auto Parts Store', 'lbc-local-seo'),
					'AutoRental'		=> __('Auto Rental', 'lbc-local-seo'),
					'AutoRepair'		=> __('Auto Repair', 'lbc-local-seo'),
					'AutoWash'			=> __('Auto Wash', 'lbc-local-seo'),
					'GasStation'		=> __('Gas Station', 'lbc-local-seo'),
					'MotorcycleDealer'	=> __('Motorcycle Dealer', 'lbc-local-seo'),
					'MotorcycleRepair'	=> __('Motorcycle Repair', 'lbc-local-seo')
				)
			),
			'ChildCare'	=> array(
				'title'		=> __('Child Care', 'lbc-local-seo')
			),
			'DryCleaningOrLaundry'	=> array(
				'title'		=> __('Dry Cleaning Or Laundry', 'lbc-local-seo')
			),
			'EmergencyService'	=> array(
				'title'		=> __('Emergency Service', 'lbc-local-seo'),
				'subtypes'	=> array(
					'FireStation'	=> __('Fire Station', 'lbc-local-seo'),
					'Hospital'		=> __('Hospital', 'lbc-local-seo'),
					'PoliceStation'	=> __('Police Station', 'lbc-local-seo')
				)
			),
			'EmploymentAgency'	=> array(
				'title'		=> __('Employment Agency', 'lbc-local-seo')
			),
			'EntertainmentBusiness'	=> array(
				'title'		=> __('Entertainment Business', 'lbc-local-seo'),
				'subtypes'	=> array(
					'AdultEntertainment'	=> __('Adult Entertainment', 'lbc-local-seo'),
					'AmusementPark'			=> __('Amusement Park', 'lbc-local-seo'),
					'ArtGallery'			=> __('Art Gallery', 'lbc-local-seo'),
					'Casino'				=> __('Casino', 'lbc-local-seo'),
					'ComedyClub'			=> __('Comedy Club', 'lbc-local-seo'),
					'MovieTheater'			=> __('Movie Theater', 'lbc-local-seo'),
					'NightClub'				=> __('Night Club', 'lbc-local-seo')
				)
			),
			'FinancialService'	=> array(
				'title'		=> __('Financial Service', 'lbc-local-seo'),
				'subtypes'	=> array(
					'AccountingService'	=> __('Accounting Service', 'lbc-local-seo'),
					'AutomatedTeller'	=> __('Automated Teller', 'lbc-local-seo'),
					'BankOrCreditUnion'	=> __('Bank Or Credit Union', 'lbc-local-seo'),
					'InsuranceAgency'	=> __('Insurance Agency', 'lbc-local-seo')
				)
			),
			'FoodEstablishment'	=> array(
				'title'		=> __('Food Establishment', 'lbc-local-seo'),
				'subtypes'	=> array(
					'Bakery'				=> __('Bakery', 'lbc-local-seo'),
					'BarOrPub'				=> __('Bar Or Pub', 'lbc-local-seo'),
					'Brewery'				=> __('Brewery', 'lbc-local-seo'),
					'CafeOrCoffeeShop'		=> __('Cafe Or Coffee Shop', 'lbc-local-seo'),
					'FastFoodRestaurant'	=> __('Fast Food Restaurant', 'lbc-local-seo'),
					'IceCreamShop'			=> __('Ice Cream Shop', 'lbc-local-seo'),
					'Restaurant'			=> __('Restaurant', 'lbc-local-seo'),
					'Winery'				=> __('Winery', 'lbc-local-seo')
				)
			),
			'GovernmentOffice'	=> array(
				'title'		=> __('Government Office', 'lbc-local-seo'),
				'subtypes'	=> array(
					'PostOffice'	=> __('Post Office', 'lbc-local-seo')
				)
			),
			'HealthAndBeautyBusiness'	=> array(
				'title'		=> __('Health And Beauty Business', 'lbc-local-seo'),
				'subtypes'	=> array(
					'BeautySalon'	=> __('Beauty Salon', 'lbc-local-seo'),
					'DaySpa'		=> __('Day Spa', 'lbc-local-seo'),
					'HairSalon'		=> __('Hair Salon', 'lbc-local-seo'),
					'HealthClub'	=> __('Health Club', 'lbc-local-seo'),
					'NailSalon'		=> __('Nail Salon', 'lbc-local-seo'),
					'TattooParlor'	=> __('Tattoo Parlor', 'lbc-local-seo')
				)
			),
			'HomeAndConstructionBusiness'	=> array(
				'title'		=> __('Home And Construction Business', 'lbc-local-seo'),
				'subtypes'	=> array(
					'Electrician'		=> __('Electrician', 'lbc-local-seo'),
					'GeneralContractor'	=> __('General Contractor', 'lbc-local-seo'),
					'HVACBusiness'		=> __('H V A C Business', 'lbc-local-seo'),
					'HousePainter'		=> __('House Painter', 'lbc-local-seo'),
					'Locksmith'			=> __('Locksmith', 'lbc-local-seo'),
					'MovingCompany'		=> __('Moving Company', 'lbc-local-seo'),
					'Plumber'			=> __('Plumber', 'lbc-local-seo'),
					'RoofingContractor'	=> __('Roofing Contractor', 'lbc-local-seo')
				)
			),
			'InternetCafe'	=> array(
				'title'		=> __('Internet Cafe', 'lbc-local-seo')
			),
			'Library'	=> array(
				'title'		=> __('Library', 'lbc-local-seo')
			),
			'LodgingBusiness'	=> array(
				'title'		=> __('Lodging Business', 'lbc-local-seo'),
				'subtypes'	=> array(
					'BedAndBreakfast'	=> __('Bed And Breakfast', 'lbc-local-seo'),
					'Hostel'			=> __('Hostel', 'lbc-local-seo'),
					'Hotel'				=> __('Hotel', 'lbc-local-seo'),
					'Motel'				=> __('Motel', 'lbc-local-seo')
				)
			),
			'MedicalOrganization'	=> array(
				'title'		=> __('Medical Organization', 'lbc-local-seo'),
				'subtypes'	=> array(
					'Dentist'			=> __('Dentist', 'lbc-local-seo'),
					'DiagnosticLab'		=> __('Diagnostic Lab', 'lbc-local-seo'),
					'Hospital'			=> __('Hospital', 'lbc-local-seo'),
					'MedicalClinic'		=> __('Medical Clinic', 'lbc-local-seo'),
					'Optician'			=> __('Optician', 'lbc-local-seo'),
					'Pharmacy'			=> __('Pharmacy', 'lbc-local-seo'),
					'Physician'			=> __('Physician', 'lbc-local-seo'),
					'VeterinaryCare'	=> __('Veterinary Care', 'lbc-local-seo')
				)
			),
			'ProfessionalService'	=> array(
				'title'		=> __('Professional Service', 'lbc-local-seo'),
				'subtypes'	=> array(
					'AccountingService'	=> __('Accounting Service', 'lbc-local-seo'),
					'Attorney'			=> __('Attorney', 'lbc-local-seo'),
					'Dentist'			=> __('Dentist', 'lbc-local-seo'),
					'Electrician'		=> __('Electrician', 'lbc-local-seo'),
					'GeneralContractor'	=> __('General Contractor', 'lbc-local-seo'),
					'HousePainter'		=> __('House Painter', 'lbc-local-seo'),
					'Locksmith'			=> __('Locksmith', 'lbc-local-seo'),
					'Notary'			=> __('Notary', 'lbc-local-seo'),
					'Plumber'			=> __('Plumber', 'lbc-local-seo'),
					'RoofingContractor'	=> __('Roofing Contractor', 'lbc-local-seo')
				)
			),
			'RadioStation'	=> array(
				'title'		=> __('Radio Station', 'lbc-local-seo')
			),
			'RealEstateAgent'	=> array(
				'title'		=> __('Real Estate Agent', 'lbc-local-seo')
			),
			'RecyclingCenter'	=> array(
				'title'		=> __('Recycling Center', 'lbc-local-seo')
			),
			'SelfStorage'	=> array(
				'title'		=> __('Self Storage', 'lbc-local-seo')
			),
			'ShoppingCenter'	=> array(
				'title'		=> __('Shopping Center', 'lbc-local-seo')
			),
			'SportsActivityLocation'	=> array(
				'title'		=> __('Sports Activity Location', 'lbc-local-seo'),
				'subtypes'	=> array(
					'BowlingAlley'			=> __('Bowling Alley', 'lbc-local-seo'),
					'ExerciseGym'			=> __('Exercise Gym', 'lbc-local-seo'),
					'GolfCourse'			=> __('Golf Course', 'lbc-local-seo'),
					'HealthClub'			=> __('Health Club', 'lbc-local-seo'),
					'PublicSwimmingPool'	=> __('Public Swimming Pool', 'lbc-local-seo'),
					'SkiResort'				=> __('Ski Resort', 'lbc-local-seo'),
					'SportsClub'			=> __('Sports Club', 'lbc-local-seo'),
					'StadiumOrArena'		=> __('Stadium Or Arena', 'lbc-local-seo'),
					'TennisComplex'			=> __('Tennis Complex', 'lbc-local-seo')
				)
			),
			'Store'	=> array(
				'title'		=> __('Store', 'lbc-local-seo'),
				'subtypes'	=> array(
					'AutoPartsStore'		=> __('Auto Parts Store', 'lbc-local-seo'),
					'BikeStore'				=> __('Bike Store', 'lbc-local-seo'),
					'BookStore'				=> __('Book Store', 'lbc-local-seo'),
					'ClothingStore'			=> __('Clothing Store', 'lbc-local-seo'),
					'ComputerStore'			=> __('Computer Store', 'lbc-local-seo'),
					'ConvenienceStore'		=> __('Convenience Store', 'lbc-local-seo'),
					'DepartmentStore'		=> __('Department Store', 'lbc-local-seo'),
					'ElectronicsStore'		=> __('Electronics Store', 'lbc-local-seo'),
					'Florist'				=> __('Florist', 'lbc-local-seo'),
					'FurnitureStore'		=> __('Furniture Store', 'lbc-local-seo'),
					'GardenStore'			=> __('Garden Store', 'lbc-local-seo'),
					'GroceryStore'			=> __('Grocery Store', 'lbc-local-seo'),
					'HardwareStore'			=> __('Hardware Store', 'lbc-local-seo'),
					'HobbyShop'				=> __('Hobby Shop', 'lbc-local-seo'),
					'HomeGoodsStore'		=> __('Home Goods Store', 'lbc-local-seo'),
					'JewelryStore'			=> __('Jewelry Store', 'lbc-local-seo'),
					'LiquorStore'			=> __('Liquor Store', 'lbc-local-seo'),
					'MensClothingStore'		=> __('Mens Clothing Store', 'lbc-local-seo'),
					'MobilePhoneStore'		=> __('Mobile Phone Store', 'lbc-local-seo'),
					'MovieRentalStore'		=> __('Movie Rental Store', 'lbc-local-seo'),
					'MusicStore'			=> __('Music Store', 'lbc-local-seo'),
					'OfficeEquipmentStore'	=> __('Office Equipment Store', 'lbc-local-seo'),
					'OutletStore'			=> __('Outlet Store', 'lbc-local-seo'),
					'PawnShop'				=> __('Pawn Shop', 'lbc-local-seo'),
					'PetStore'				=> __('Pet Store', 'lbc-local-seo'),
					'ShoeStore'				=> __('Shoe Store', 'lbc-local-seo'),
					'SportingGoodsStore'	=> __('Sporting Goods Store', 'lbc-local-seo'),
					'TireShop'				=> __('Tire Shop', 'lbc-local-seo'),
					'ToyStore'				=> __('Toy Store', 'lbc-local-seo'),
					'WholesaleStore'		=> __('Wholesale Store', 'lbc-local-seo')
				)
			),
			'TelevisionStation'	=> array(
				'title'		=> __('Television Station', 'lbc-local-seo')
			),
			'TouristInformationCenter'	=> array(
				'title'		=> __('Tourist Information Center', 'lbc-local-seo')
			),
			'TravelAgency'	=> array(
				'title'		=> __('Travel Agency', 'lbc-local-seo')
			),
			'LocalBusiness'	=> array(
				'title'		=> __('Other (Local Business)', 'lbc-local-seo')
			)
		);
	}
	
	/**
	 * Returns contries.
	 * 
	 * @since 1.0
	 * 
	 * @return array
	 */
	public function get_countries()
	{
		return array(
			''		=> '',
			'AF'	=> __('Afghanistan', 'lbc-local-seo'),
			'AX'	=> __('Åland Islands', 'lbc-local-seo'),
			'AL'	=> __('Albania', 'lbc-local-seo'),
			'DZ'	=> __('Algeria', 'lbc-local-seo'),
			'AS'	=> __('American Samoa', 'lbc-local-seo'),
			'AD'	=> __('Andorra', 'lbc-local-seo'),
			'AO'	=> __('Angola', 'lbc-local-seo'),
			'AI'	=> __('Anguilla', 'lbc-local-seo'),
			'AQ'	=> __('Antarctica', 'lbc-local-seo'),
			'AG'	=> __('Antigua and Barbuda', 'lbc-local-seo'),
			'AR'	=> __('Argentina', 'lbc-local-seo'),
			'AM'	=> __('Armenia', 'lbc-local-seo'),
			'AW'	=> __('Aruba', 'lbc-local-seo'),
			'AU'	=> __('Australia', 'lbc-local-seo'),
			'AT'	=> __('Austria', 'lbc-local-seo'),
			'AZ'	=> __('Azerbaijan', 'lbc-local-seo'),
			'BS'	=> __('Bahamas', 'lbc-local-seo'),
			'BH'	=> __('Bahrain', 'lbc-local-seo'),
			'BD'	=> __('Bangladesh', 'lbc-local-seo'),
			'BB'	=> __('Barbados', 'lbc-local-seo'),
			'BY'	=> __('Belarus', 'lbc-local-seo'),
			'BE'	=> __('Belgium', 'lbc-local-seo'),
			'BZ'	=> __('Belize', 'lbc-local-seo'),
			'BJ'	=> __('Benin', 'lbc-local-seo'),
			'BM'	=> __('Bermuda', 'lbc-local-seo'),
			'BT'	=> __('Bhutan', 'lbc-local-seo'),
			'BO'	=> __('Bolivia, Plurinational State of', 'lbc-local-seo'),
			'BQ'	=> __('Bonaire, Sint Eustatius and Saba', 'lbc-local-seo'),
			'BA'	=> __('Bosnia and Herzegovina', 'lbc-local-seo'),
			'BW'	=> __('Botswana', 'lbc-local-seo'),
			'BV'	=> __('Bouvet Island', 'lbc-local-seo'),
			'BR'	=> __('Brazil', 'lbc-local-seo'),
			'IO'	=> __('British Indian Ocean Territory', 'lbc-local-seo'),
			'BN'	=> __('Brunei Darussalam', 'lbc-local-seo'),
			'BG'	=> __('Bulgaria', 'lbc-local-seo'),
			'BF'	=> __('Burkina Faso', 'lbc-local-seo'),
			'BI'	=> __('Burundi', 'lbc-local-seo'),
			'KH'	=> __('Cambodia', 'lbc-local-seo'),
			'CM'	=> __('Cameroon', 'lbc-local-seo'),
			'CA'	=> __('Canada', 'lbc-local-seo'),
			'CV'	=> __('Cape Verde', 'lbc-local-seo'),
			'KY'	=> __('Cayman Islands', 'lbc-local-seo'),
			'CF'	=> __('Central African Republic', 'lbc-local-seo'),
			'TD'	=> __('Chad', 'lbc-local-seo'),
			'CL'	=> __('Chile', 'lbc-local-seo'),
			'CN'	=> __('China', 'lbc-local-seo'),
			'CX'	=> __('Christmas Island', 'lbc-local-seo'),
			'CC'	=> __('Cocos (Keeling) Islands', 'lbc-local-seo'),
			'CO'	=> __('Colombia', 'lbc-local-seo'),
			'KM'	=> __('Comoros', 'lbc-local-seo'),
			'CG'	=> __('Congo', 'lbc-local-seo'),
			'CD'	=> __('Congo, the Democratic Republic of the', 'lbc-local-seo'),
			'CK'	=> __('Cook Islands', 'lbc-local-seo'),
			'CR'	=> __('Costa Rica', 'lbc-local-seo'),
			'CI'	=> __('Côte d\'Ivoire', 'lbc-local-seo'),
			'HR'	=> __('Croatia', 'lbc-local-seo'),
			'CU'	=> __('Cuba', 'lbc-local-seo'),
			'CW'	=> __('Curaçao', 'lbc-local-seo'),
			'CY'	=> __('Cyprus', 'lbc-local-seo'),
			'CZ'	=> __('Czech Republic', 'lbc-local-seo'),
			'DK'	=> __('Denmark', 'lbc-local-seo'),
			'DJ'	=> __('Djibouti', 'lbc-local-seo'),
			'DM'	=> __('Dominica', 'lbc-local-seo'),
			'DO'	=> __('Dominican Republic', 'lbc-local-seo'),
			'EC'	=> __('Ecuador', 'lbc-local-seo'),
			'EG'	=> __('Egypt', 'lbc-local-seo'),
			'SV'	=> __('El Salvador', 'lbc-local-seo'),
			'GQ'	=> __('Equatorial Guinea', 'lbc-local-seo'),
			'ER'	=> __('Eritrea', 'lbc-local-seo'),
			'EE'	=> __('Estonia', 'lbc-local-seo'),
			'ET'	=> __('Ethiopia', 'lbc-local-seo'),
			'FK'	=> __('Falkland Islands (Malvinas)', 'lbc-local-seo'),
			'FO'	=> __('Faroe Islands', 'lbc-local-seo'),
			'FJ'	=> __('Fiji', 'lbc-local-seo'),
			'FI'	=> __('Finland', 'lbc-local-seo'),
			'FR'	=> __('France', 'lbc-local-seo'),
			'GF'	=> __('French Guiana', 'lbc-local-seo'),
			'PF'	=> __('French Polynesia', 'lbc-local-seo'),
			'TF'	=> __('French Southern Territories', 'lbc-local-seo'),
			'GA'	=> __('Gabon', 'lbc-local-seo'),
			'GM'	=> __('Gambia', 'lbc-local-seo'),
			'GE'	=> __('Georgia', 'lbc-local-seo'),
			'DE'	=> __('Germany', 'lbc-local-seo'),
			'GH'	=> __('Ghana', 'lbc-local-seo'),
			'GI'	=> __('Gibraltar', 'lbc-local-seo'),
			'GR'	=> __('Greece', 'lbc-local-seo'),
			'GL'	=> __('Greenland', 'lbc-local-seo'),
			'GD'	=> __('Grenada', 'lbc-local-seo'),
			'GP'	=> __('Guadeloupe', 'lbc-local-seo'),
			'GU'	=> __('Guam', 'lbc-local-seo'),
			'GT'	=> __('Guatemala', 'lbc-local-seo'),
			'GG'	=> __('Guernsey', 'lbc-local-seo'),
			'GN'	=> __('Guinea', 'lbc-local-seo'),
			'GW'	=> __('Guinea-Bissau', 'lbc-local-seo'),
			'GY'	=> __('Guyana', 'lbc-local-seo'),
			'HT'	=> __('Haiti', 'lbc-local-seo'),
			'HM'	=> __('Heard Island and McDonald Islands', 'lbc-local-seo'),
			'VA'	=> __('Holy See (Vatican City State)', 'lbc-local-seo'),
			'HN'	=> __('Honduras', 'lbc-local-seo'),
			'HK'	=> __('Hong Kong', 'lbc-local-seo'),
			'HU'	=> __('Hungary', 'lbc-local-seo'),
			'IS'	=> __('Iceland', 'lbc-local-seo'),
			'IN'	=> __('India', 'lbc-local-seo'),
			'ID'	=> __('Indonesia', 'lbc-local-seo'),
			'IR'	=> __('Iran, Islamic Republic of', 'lbc-local-seo'),
			'IQ'	=> __('Iraq', 'lbc-local-seo'),
			'IE'	=> __('Ireland', 'lbc-local-seo'),
			'IM'	=> __('Isle of Man', 'lbc-local-seo'),
			'IL'	=> __('Israel', 'lbc-local-seo'),
			'IT'	=> __('Italy', 'lbc-local-seo'),
			'JM'	=> __('Jamaica', 'lbc-local-seo'),
			'JP'	=> __('Japan', 'lbc-local-seo'),
			'JE'	=> __('Jersey', 'lbc-local-seo'),
			'JO'	=> __('Jordan', 'lbc-local-seo'),
			'KZ'	=> __('Kazakhstan', 'lbc-local-seo'),
			'KE'	=> __('Kenya', 'lbc-local-seo'),
			'KI'	=> __('Kiribati', 'lbc-local-seo'),
			'KP'	=> __('Korea, Democratic People\'s Republic of', 'lbc-local-seo'),
			'KR'	=> __('Korea, Republic of', 'lbc-local-seo'),
			'KW'	=> __('Kuwait', 'lbc-local-seo'),
			'KG'	=> __('Kyrgyzstan', 'lbc-local-seo'),
			'LA'	=> __('Lao People\'s Democratic Republic', 'lbc-local-seo'),
			'LV'	=> __('Latvia', 'lbc-local-seo'),
			'LB'	=> __('Lebanon', 'lbc-local-seo'),
			'LS'	=> __('Lesotho', 'lbc-local-seo'),
			'LR'	=> __('Liberia', 'lbc-local-seo'),
			'LY'	=> __('Libya', 'lbc-local-seo'),
			'LI'	=> __('Liechtenstein', 'lbc-local-seo'),
			'LT'	=> __('Lithuania', 'lbc-local-seo'),
			'LU'	=> __('Luxembourg', 'lbc-local-seo'),
			'MO'	=> __('Macao', 'lbc-local-seo'),
			'MK'	=> __('Macedonia, the former Yugoslav Republic of', 'lbc-local-seo'),
			'MG'	=> __('Madagascar', 'lbc-local-seo'),
			'MW'	=> __('Malawi', 'lbc-local-seo'),
			'MY'	=> __('Malaysia', 'lbc-local-seo'),
			'MV'	=> __('Maldives', 'lbc-local-seo'),
			'ML'	=> __('Mali', 'lbc-local-seo'),
			'MT'	=> __('Malta', 'lbc-local-seo'),
			'MH'	=> __('Marshall Islands', 'lbc-local-seo'),
			'MQ'	=> __('Martinique', 'lbc-local-seo'),
			'MR'	=> __('Mauritania', 'lbc-local-seo'),
			'MU'	=> __('Mauritius', 'lbc-local-seo'),
			'YT'	=> __('Mayotte', 'lbc-local-seo'),
			'MX'	=> __('Mexico', 'lbc-local-seo'),
			'FM'	=> __('Micronesia, Federated States of', 'lbc-local-seo'),
			'MD'	=> __('Moldova, Republic of', 'lbc-local-seo'),
			'MC'	=> __('Monaco', 'lbc-local-seo'),
			'MN'	=> __('Mongolia', 'lbc-local-seo'),
			'ME'	=> __('Montenegro', 'lbc-local-seo'),
			'MS'	=> __('Montserrat', 'lbc-local-seo'),
			'MA'	=> __('Morocco', 'lbc-local-seo'),
			'MZ'	=> __('Mozambique', 'lbc-local-seo'),
			'MM'	=> __('Myanmar', 'lbc-local-seo'),
			'NA'	=> __('Namibia', 'lbc-local-seo'),
			'NR'	=> __('Nauru', 'lbc-local-seo'),
			'NP'	=> __('Nepal', 'lbc-local-seo'),
			'NL'	=> __('Netherlands', 'lbc-local-seo'),
			'NC'	=> __('New Caledonia', 'lbc-local-seo'),
			'NZ'	=> __('New Zealand', 'lbc-local-seo'),
			'NI'	=> __('Nicaragua', 'lbc-local-seo'),
			'NE'	=> __('Niger', 'lbc-local-seo'),
			'NG'	=> __('Nigeria', 'lbc-local-seo'),
			'NU'	=> __('Niue', 'lbc-local-seo'),
			'NF'	=> __('Norfolk Island', 'lbc-local-seo'),
			'MP'	=> __('Northern Mariana Islands', 'lbc-local-seo'),
			'NO'	=> __('Norway', 'lbc-local-seo'),
			'OM'	=> __('Oman', 'lbc-local-seo'),
			'PK'	=> __('Pakistan', 'lbc-local-seo'),
			'PW'	=> __('Palau', 'lbc-local-seo'),
			'PS'	=> __('Palestine, State of', 'lbc-local-seo'),
			'PA'	=> __('Panama', 'lbc-local-seo'),
			'PG'	=> __('Papua New Guinea', 'lbc-local-seo'),
			'PY'	=> __('Paraguay', 'lbc-local-seo'),
			'PE'	=> __('Peru', 'lbc-local-seo'),
			'PH'	=> __('Philippines', 'lbc-local-seo'),
			'PN'	=> __('Pitcairn', 'lbc-local-seo'),
			'PL'	=> __('Poland', 'lbc-local-seo'),
			'PT'	=> __('Portugal', 'lbc-local-seo'),
			'PR'	=> __('Puerto Rico', 'lbc-local-seo'),
			'QA'	=> __('Qatar', 'lbc-local-seo'),
			'RE'	=> __('Réunion', 'lbc-local-seo'),
			'RO'	=> __('Romania', 'lbc-local-seo'),
			'RU'	=> __('Russian Federation', 'lbc-local-seo'),
			'RW'	=> __('Rwanda', 'lbc-local-seo'),
			'BL'	=> __('Saint Barthélemy', 'lbc-local-seo'),
			'SH'	=> __('Saint Helena, Ascension and Tristan da Cunha', 'lbc-local-seo'),
			'KN'	=> __('Saint Kitts and Nevis', 'lbc-local-seo'),
			'LC'	=> __('Saint Lucia', 'lbc-local-seo'),
			'MF'	=> __('Saint Martin (French part)', 'lbc-local-seo'),
			'PM'	=> __('Saint Pierre and Miquelon', 'lbc-local-seo'),
			'VC'	=> __('Saint Vincent and the Grenadines', 'lbc-local-seo'),
			'WS'	=> __('Samoa', 'lbc-local-seo'),
			'SM'	=> __('San Marino', 'lbc-local-seo'),
			'ST'	=> __('Sao Tome and Principe', 'lbc-local-seo'),
			'SA'	=> __('Saudi Arabia', 'lbc-local-seo'),
			'SN'	=> __('Senegal', 'lbc-local-seo'),
			'RS'	=> __('Serbia', 'lbc-local-seo'),
			'SC'	=> __('Seychelles', 'lbc-local-seo'),
			'SL'	=> __('Sierra Leone', 'lbc-local-seo'),
			'SG'	=> __('Singapore', 'lbc-local-seo'),
			'SX'	=> __('Sint Maarten (Dutch part)', 'lbc-local-seo'),
			'SK'	=> __('Slovakia', 'lbc-local-seo'),
			'SI'	=> __('Slovenia', 'lbc-local-seo'),
			'SB'	=> __('Solomon Islands', 'lbc-local-seo'),
			'SO'	=> __('Somalia', 'lbc-local-seo'),
			'ZA'	=> __('South Africa', 'lbc-local-seo'),
			'GS'	=> __('South Georgia and the South Sandwich Islands', 'lbc-local-seo'),
			'SS'	=> __('South Sudan', 'lbc-local-seo'),
			'ES'	=> __('Spain', 'lbc-local-seo'),
			'LK'	=> __('Sri Lanka', 'lbc-local-seo'),
			'SD'	=> __('Sudan', 'lbc-local-seo'),
			'SR'	=> __('Suriname', 'lbc-local-seo'),
			'SJ'	=> __('Svalbard and Jan Mayen', 'lbc-local-seo'),
			'SZ'	=> __('Swaziland', 'lbc-local-seo'),
			'SE'	=> __('Sweden', 'lbc-local-seo'),
			'CH'	=> __('Switzerland', 'lbc-local-seo'),
			'SY'	=> __('Syrian Arab Republic', 'lbc-local-seo'),
			'TW'	=> __('Taiwan, Province of China', 'lbc-local-seo'),
			'TJ'	=> __('Tajikistan', 'lbc-local-seo'),
			'TZ'	=> __('Tanzania, United Republic of', 'lbc-local-seo'),
			'TH'	=> __('Thailand', 'lbc-local-seo'),
			'TL'	=> __('Timor-Leste', 'lbc-local-seo'),
			'TG'	=> __('Togo', 'lbc-local-seo'),
			'TK'	=> __('Tokelau', 'lbc-local-seo'),
			'TO'	=> __('Tonga', 'lbc-local-seo'),
			'TT'	=> __('Trinidad and Tobago', 'lbc-local-seo'),
			'TN'	=> __('Tunisia', 'lbc-local-seo'),
			'TR'	=> __('Turkey', 'lbc-local-seo'),
			'TM'	=> __('Turkmenistan', 'lbc-local-seo'),
			'TC'	=> __('Turks and Caicos Islands', 'lbc-local-seo'),
			'TV'	=> __('Tuvalu', 'lbc-local-seo'),
			'UG'	=> __('Uganda', 'lbc-local-seo'),
			'UA'	=> __('Ukraine', 'lbc-local-seo'),
			'AE'	=> __('United Arab Emirates', 'lbc-local-seo'),
			'GB'	=> __('United Kingdom', 'lbc-local-seo'),
			'US'	=> __('United States', 'lbc-local-seo'),
			'UM'	=> __('United States Minor Outlying Islands', 'lbc-local-seo'),
			'UY'	=> __('Uruguay', 'lbc-local-seo'),
			'UZ'	=> __('Uzbekistan', 'lbc-local-seo'),
			'VU'	=> __('Vanuatu', 'lbc-local-seo'),
			'VE'	=> __('Venezuela, Bolivarian Republic of', 'lbc-local-seo'),
			'VN'	=> __('Viet Nam', 'lbc-local-seo'),
			'VG'	=> __('Virgin Islands, British', 'lbc-local-seo'),
			'VI'	=> __('Virgin Islands, U.S.', 'lbc-local-seo'),
			'WF'	=> __('Wallis and Futuna', 'lbc-local-seo'),
			'EH'	=> __('Western Sahara', 'lbc-local-seo'),
			'YE'	=> __('Yemen', 'lbc-local-seo'),
			'ZM'	=> __('Zambia', 'lbc-local-seo'),
			'ZW'	=> __('Zimbabwe', 'lbc-local-seo')
		);
	}
	
	/**
	 * Returns currencies.
	 * 
	 * @since 1.0
	 * 
	 * @return array
	 */
	public function get_currencies()
	{
		return array(
			'AED'	=> __('United Arab Emirates dirham', 'lbc-local-seo'),
			'AFN'	=> __('Afghan afghani', 'lbc-local-seo'),
			'ALL'	=> __('Albanian lek', 'lbc-local-seo'),
			'AMD'	=> __('Armenian dram', 'lbc-local-seo'),
			'ANG'	=> __('Netherlands Antillean guilder', 'lbc-local-seo'),
			'AOA'	=> __('Angolan kwanza', 'lbc-local-seo'),
			'ARS'	=> __('Argentine peso', 'lbc-local-seo'),
			'AUD'	=> __('Australian dollar', 'lbc-local-seo'),
			'AWG'	=> __('Aruban florin', 'lbc-local-seo'),
			'AZN'	=> __('Azerbaijani manat', 'lbc-local-seo'),
			'BAM'	=> __('Bosnia and Herzegovina convertible mark', 'lbc-local-seo'),
			'BBD'	=> __('Barbados dollar', 'lbc-local-seo'),
			'BDT'	=> __('Bangladeshi taka', 'lbc-local-seo'),
			'BGN'	=> __('Bulgarian lev', 'lbc-local-seo'),
			'BHD'	=> __('Bahraini dinar', 'lbc-local-seo'),
			'BIF'	=> __('Burundian franc', 'lbc-local-seo'),
			'BMD'	=> __('Bermudian dollar', 'lbc-local-seo'),
			'BND'	=> __('Brunei dollar', 'lbc-local-seo'),
			'BOB'	=> __('Boliviano', 'lbc-local-seo'),
			'BOV'	=> __('Bolivian Mvdol (funds code)', 'lbc-local-seo'),
			'BRL'	=> __('Brazilian real', 'lbc-local-seo'),
			'BSD'	=> __('Bahamian dollar', 'lbc-local-seo'),
			'BTN'	=> __('Bhutanese ngultrum', 'lbc-local-seo'),
			'BWP'	=> __('Botswana pula', 'lbc-local-seo'),
			'BYR'	=> __('Belarusian ruble', 'lbc-local-seo'),
			'BZD'	=> __('Belize dollar', 'lbc-local-seo'),
			'CAD'	=> __('Canadian dollar', 'lbc-local-seo'),
			'CDF'	=> __('Congolese franc', 'lbc-local-seo'),
			'CHE'	=> __('WIR Euro (complementary currency)', 'lbc-local-seo'),
			'CHF'	=> __('Swiss franc', 'lbc-local-seo'),
			'CHW'	=> __('WIR Franc (complementary currency)', 'lbc-local-seo'),
			'CLF'	=> __('Unidad de Fomento (funds code)', 'lbc-local-seo'),
			'CLP'	=> __('Chilean peso', 'lbc-local-seo'),
			'CNY'	=> __('Chinese yuan', 'lbc-local-seo'),
			'COP'	=> __('Colombian peso', 'lbc-local-seo'),
			'COU'	=> __('Unidad de Valor Real (UVR) (funds code)[7]', 'lbc-local-seo'),
			'CRC'	=> __('Costa Rican colon', 'lbc-local-seo'),
			'CUC'	=> __('Cuban convertible peso', 'lbc-local-seo'),
			'CUP'	=> __('Cuban peso', 'lbc-local-seo'),
			'CVE'	=> __('Cape Verde escudo', 'lbc-local-seo'),
			'CZK'	=> __('Czech koruna', 'lbc-local-seo'),
			'DJF'	=> __('Djiboutian franc', 'lbc-local-seo'),
			'DKK'	=> __('Danish krone', 'lbc-local-seo'),
			'DOP'	=> __('Dominican peso', 'lbc-local-seo'),
			'DZD'	=> __('Algerian dinar', 'lbc-local-seo'),
			'EGP'	=> __('Egyptian pound', 'lbc-local-seo'),
			'ERN'	=> __('Eritrean nakfa', 'lbc-local-seo'),
			'ETB'	=> __('Ethiopian birr', 'lbc-local-seo'),
			'EUR'	=> __('Euro', 'lbc-local-seo'),
			'FJD'	=> __('Fiji dollar', 'lbc-local-seo'),
			'FKP'	=> __('Falkland Islands pound', 'lbc-local-seo'),
			'GBP'	=> __('Pound sterling', 'lbc-local-seo'),
			'GEL'	=> __('Georgian lari', 'lbc-local-seo'),
			'GHS'	=> __('Ghanaian cedi', 'lbc-local-seo'),
			'GIP'	=> __('Gibraltar pound', 'lbc-local-seo'),
			'GMD'	=> __('Gambian dalasi', 'lbc-local-seo'),
			'GNF'	=> __('Guinean franc', 'lbc-local-seo'),
			'GTQ'	=> __('Guatemalan quetzal', 'lbc-local-seo'),
			'GYD'	=> __('Guyanese dollar', 'lbc-local-seo'),
			'HKD'	=> __('Hong Kong dollar', 'lbc-local-seo'),
			'HNL'	=> __('Honduran lempira', 'lbc-local-seo'),
			'HRK'	=> __('Croatian kuna', 'lbc-local-seo'),
			'HTG'	=> __('Haitian gourde', 'lbc-local-seo'),
			'HUF'	=> __('Hungarian forint', 'lbc-local-seo'),
			'IDR'	=> __('Indonesian rupiah', 'lbc-local-seo'),
			'ILS'	=> __('Israeli new shekel', 'lbc-local-seo'),
			'INR'	=> __('Indian rupee', 'lbc-local-seo'),
			'IQD'	=> __('Iraqi dinar', 'lbc-local-seo'),
			'IRR'	=> __('Iranian rial', 'lbc-local-seo'),
			'ISK'	=> __('Icelandic króna', 'lbc-local-seo'),
			'JMD'	=> __('Jamaican dollar', 'lbc-local-seo'),
			'JOD'	=> __('Jordanian dinar', 'lbc-local-seo'),
			'JPY'	=> __('Japanese yen', 'lbc-local-seo'),
			'KES'	=> __('Kenyan shilling', 'lbc-local-seo'),
			'KGS'	=> __('Kyrgyzstani som', 'lbc-local-seo'),
			'KHR'	=> __('Cambodian riel', 'lbc-local-seo'),
			'KMF'	=> __('Comoro franc', 'lbc-local-seo'),
			'KPW'	=> __('North Korean won', 'lbc-local-seo'),
			'KRW'	=> __('South Korean won', 'lbc-local-seo'),
			'KWD'	=> __('Kuwaiti dinar', 'lbc-local-seo'),
			'KYD'	=> __('Cayman Islands dollar', 'lbc-local-seo'),
			'KZT'	=> __('Kazakhstani tenge', 'lbc-local-seo'),
			'LAK'	=> __('Lao kip', 'lbc-local-seo'),
			'LBP'	=> __('Lebanese pound', 'lbc-local-seo'),
			'LKR'	=> __('Sri Lankan rupee', 'lbc-local-seo'),
			'LRD'	=> __('Liberian dollar', 'lbc-local-seo'),
			'LSL'	=> __('Lesotho loti', 'lbc-local-seo'),
			'LTL'	=> __('Lithuanian litas', 'lbc-local-seo'),
			'LVL'	=> __('Latvian lats', 'lbc-local-seo'),
			'LYD'	=> __('Libyan dinar', 'lbc-local-seo'),
			'MAD'	=> __('Moroccan dirham', 'lbc-local-seo'),
			'MDL'	=> __('Moldovan leu', 'lbc-local-seo'),
			'MGA'	=> __('Malagasy ariary', 'lbc-local-seo'),
			'MKD'	=> __('Macedonian denar', 'lbc-local-seo'),
			'MMK'	=> __('Myanma kyat', 'lbc-local-seo'),
			'MNT'	=> __('Mongolian tugrik', 'lbc-local-seo'),
			'MOP'	=> __('Macanese pataca', 'lbc-local-seo'),
			'MRO'	=> __('Mauritanian ouguiya', 'lbc-local-seo'),
			'MUR'	=> __('Mauritian rupee', 'lbc-local-seo'),
			'MVR'	=> __('Maldivian rufiyaa', 'lbc-local-seo'),
			'MWK'	=> __('Malawian kwacha', 'lbc-local-seo'),
			'MXN'	=> __('Mexican peso', 'lbc-local-seo'),
			'MXV'	=> __('Mexican Unidad de Inversion (UDI) (funds code)', 'lbc-local-seo'),
			'MYR'	=> __('Malaysian ringgit', 'lbc-local-seo'),
			'MZN'	=> __('Mozambican metical', 'lbc-local-seo'),
			'NAD'	=> __('Namibian dollar', 'lbc-local-seo'),
			'NGN'	=> __('Nigerian naira', 'lbc-local-seo'),
			'NIO'	=> __('Nicaraguan córdoba', 'lbc-local-seo'),
			'NOK'	=> __('Norwegian krone', 'lbc-local-seo'),
			'NPR'	=> __('Nepalese rupee', 'lbc-local-seo'),
			'NZD'	=> __('New Zealand dollar', 'lbc-local-seo'),
			'OMR'	=> __('Omani rial', 'lbc-local-seo'),
			'PAB'	=> __('Panamanian balboa', 'lbc-local-seo'),
			'PEN'	=> __('Peruvian nuevo sol', 'lbc-local-seo'),
			'PGK'	=> __('Papua New Guinean kina', 'lbc-local-seo'),
			'PHP'	=> __('Philippine peso', 'lbc-local-seo'),
			'PKR'	=> __('Pakistani rupee', 'lbc-local-seo'),
			'PLN'	=> __('Polish złoty', 'lbc-local-seo'),
			'PYG'	=> __('Paraguayan guaraní', 'lbc-local-seo'),
			'QAR'	=> __('Qatari riyal', 'lbc-local-seo'),
			'RON'	=> __('Romanian new leu', 'lbc-local-seo'),
			'RSD'	=> __('Serbian dinar', 'lbc-local-seo'),
			'RUB'	=> __('Russian rouble', 'lbc-local-seo'),
			'RWF'	=> __('Rwandan franc', 'lbc-local-seo'),
			'SAR'	=> __('Saudi riyal', 'lbc-local-seo'),
			'SBD'	=> __('Solomon Islands dollar', 'lbc-local-seo'),
			'SCR'	=> __('Seychelles rupee', 'lbc-local-seo'),
			'SDG'	=> __('Sudanese pound', 'lbc-local-seo'),
			'SEK'	=> __('Swedish krona/kronor', 'lbc-local-seo'),
			'SGD'	=> __('Singapore dollar', 'lbc-local-seo'),
			'SHP'	=> __('Saint Helena pound', 'lbc-local-seo'),
			'SLL'	=> __('Sierra Leonean leone', 'lbc-local-seo'),
			'SOS'	=> __('Somali shilling', 'lbc-local-seo'),
			'SRD'	=> __('Surinamese dollar', 'lbc-local-seo'),
			'SSP'	=> __('South Sudanese pound', 'lbc-local-seo'),
			'STD'	=> __('São Tomé and Príncipe dobra', 'lbc-local-seo'),
			'SYP'	=> __('Syrian pound', 'lbc-local-seo'),
			'SZL'	=> __('Swazi lilangeni', 'lbc-local-seo'),
			'THB'	=> __('Thai baht', 'lbc-local-seo'),
			'TJS'	=> __('Tajikistani somoni', 'lbc-local-seo'),
			'TMT'	=> __('Turkmenistani manat', 'lbc-local-seo'),
			'TND'	=> __('Tunisian dinar', 'lbc-local-seo'),
			'TOP'	=> __('Tongan paʻanga', 'lbc-local-seo'),
			'TRY'	=> __('Turkish lira', 'lbc-local-seo'),
			'TTD'	=> __('Trinidad and Tobago dollar', 'lbc-local-seo'),
			'TWD'	=> __('New Taiwan dollar', 'lbc-local-seo'),
			'TZS'	=> __('Tanzanian shilling', 'lbc-local-seo'),
			'UAH'	=> __('Ukrainian hryvnia', 'lbc-local-seo'),
			'UGX'	=> __('Ugandan shilling', 'lbc-local-seo'),
			'USD'	=> __('United States dollar', 'lbc-local-seo'),
			'USN'	=> __('United States dollar (next day) (funds code)', 'lbc-local-seo'),
			'USS'	=> __('United States dollar (same day) (funds code)', 'lbc-local-seo'),
			'UYI'	=> __('Uruguay Peso en Unidades Indexadas (URUIURUI) (funds code)', 'lbc-local-seo'),
			'UYU'	=> __('Uruguayan peso', 'lbc-local-seo'),
			'UZS'	=> __('Uzbekistan som', 'lbc-local-seo'),
			'VEF'	=> __('Venezuelan bolívar', 'lbc-local-seo'),
			'VND'	=> __('Vietnamese dong', 'lbc-local-seo'),
			'VUV'	=> __('Vanuatu vatu', 'lbc-local-seo'),
			'WST'	=> __('Samoan tala', 'lbc-local-seo'),
			'XAF'	=> __('CFA franc BEAC', 'lbc-local-seo'),
			'XAG'	=> __('Silver (one troy ounce)', 'lbc-local-seo'),
			'XAU'	=> __('Gold (one troy ounce)', 'lbc-local-seo'),
			'XBA'	=> __('European Composite Unit (EURCO) (bond market unit)', 'lbc-local-seo'),
			'XBB'	=> __('European Monetary Unit (E.M.U.-6) (bond market unit)', 'lbc-local-seo'),
			'XBC'	=> __('European Unit of Account 9 (E.U.A.-9) (bond market unit)', 'lbc-local-seo'),
			'XBD'	=> __('European Unit of Account 17 (E.U.A.-17) (bond market unit)', 'lbc-local-seo'),
			'XCD'	=> __('East Caribbean dollar', 'lbc-local-seo'),
			'XDR'	=> __('Special drawing rights', 'lbc-local-seo'),
			'XFU'	=> __('UIC franc (special settlement currency)', 'lbc-local-seo'),
			'XOF'	=> __('CFA franc BCEAO', 'lbc-local-seo'),
			'XPD'	=> __('Palladium (one troy ounce)', 'lbc-local-seo'),
			'XPF'	=> __('CFP franc (franc Pacifique)', 'lbc-local-seo'),
			'XPT'	=> __('Platinum (one troy ounce)', 'lbc-local-seo'),
			'XTS'	=> __('Code reserved for testing purposes', 'lbc-local-seo'),
			'XXX'	=> __('No currency', 'lbc-local-seo'),
			'YER'	=> __('Yemeni rial', 'lbc-local-seo'),
			'ZAR'	=> __('South African rand', 'lbc-local-seo'),
			'ZMW'	=> __('Zambian kwacha', 'lbc-local-seo')
		);
	}
	
	/**
	 * Retuns price range.
	 * 
	 * @since 1.0
	 * 
	 * @return array
	 */
	public function get_price_ranges()
	{
		return array(
			'', '$', '$$', '$$$', '$$$$', '$$$$$'
		);
	}
	
	/**
	 * Returns the details of the social icons.
	 * 
	 * @since 1.0
	 * 
	 * @return array
	 */
	public function get_social_icons()
	{
		return array(
			'facebook'			=> array(
				'icon'	=> 'facebook',
				'title'	=> 'Facebook'
			),
			'twitter'			=> array(
				'icon'	=> 'twitter',
				'title'	=> 'Twitter'
			),
			'youtube'			=> array(
				'icon'	=> 'youtube',
				'title'	=> 'YouTube'
			),
			'google_plus'		=> array(
				'icon'	=> 'google-plus',
				'title'	=> 'Google+'
			),
			'google_places'		=> array(
				'icon'	=> 'google-places',
				'title'	=> 'Google Places'
			),
			'linkedin'			=> array(
				'icon'	=> 'linkedin',
				'title'	=> 'LinkedIn'
			),
			'pinterest'			=> array(
				'icon'	=> 'pinterest',
				'title'	=> 'Pinterest'
			),
			'foursquare'		=> array(
				'icon'	=> 'foursquare',
				'title'	=> 'Foursquare'
			),
			'yelp'				=> array(
				'icon'	=> 'yelp',
				'title'	=> 'Yelp'
			),
			'hotfrog'			=> array(
				'icon'	=> 'hotfrog',
				'title'	=> 'Hotfrog'
			),
			'merchant_circle'	=> array(
				'icon'	=> 'merchant-circle',
				'title'	=> 'Merchant Circle'
			),
			'digg'				=> array(
				'icon'	=> 'digg',
				'title'	=> 'Digg'
			),
			'tumblr'			=> array(
				'icon'	=> 'tumblr',
				'title'	=> 'Tumblr'
			),
			'stumbleupon'		=> array(
				'icon'	=> 'stumbleupon',
				'title'	=> 'Stumbleupon'
			),
			'flickr'			=> array(
				'icon'	=> 'flickr',
				'title'	=> 'Flickr'
			),
			'delicious'			=> array(
				'icon'	=> 'delicious',
				'title'	=> 'Del.icio.us'
			)
		);
	}
	
	/**
	 * Returns opening hours based on the hour-format (12/24).
	 * 
	 * @param int $opening_hours_format 12 or 24.
	 * 
	 * @since 1.0
	 * 
	 * @return array
	 */
	public function get_opening_hours($opening_hours_format)
	{
		if (!in_array($opening_hours_format, array(12, 24))) {
			$opening_hours_format = $this->_default_settings_form_values['opening_hours_format'];
		}
		
		// format, days
		$ret_arr = array(
			'format'	=> $opening_hours_format,
			'days'		=> $this->_days
		);
		
		// hours
		$ret_arr['hours'] = array('');
		if ($opening_hours_format == 12) {
			$from = 1;
			$to = 12;
		}
		else {
			$from = 0;
			$to = 23;
		}
		for ($i = $from; $i <= $to; $i++) {
			$ret_arr['hours'][] = (string)$i;
		}
		
		// minutes
		$ret_arr['minutes'] = array('');
		for ($i = 0; $i <= 45; $i += 15) {
			$ret_arr['minutes'][] = (string)$i;
		}
		
		// am/pm
		if ($opening_hours_format == 12) {
			$ret_arr['am_pms'] = array('', 'am', 'pm');
		}
		
		return $ret_arr;
	}
	
	/**
	 * Returns ajax messages.
	 * Purpose: make the ajax messages translateable.
	 * 
	 * @since 1.0
	 * 
	 * @return array
	 */
	public function get_ajax_msgs()
	{
		return array(
			'save_in_progress'	=> __('Please wait! Save in progress...', 'lbc-local-seo'),
			'success'			=> __('Success.', 'lbc-local-seo'),
			'server_error'		=> __('An error occurred (server side).', 'lbc-local-seo'),
			'ajax_error'		=> __('An error occurred (ajax).', 'lbc-local-seo'),
			'reload'			=> __('Reloading the page...', 'lbc-local-seo')
		);
	}
	
	/**
	 * Returns address parameters (name, schema.org itemprop, hCard class).
	 * 
	 * @since 1.0
	 * 
	 * @return array
	 */
	public function get_addr_params()
	{
		return array(
			array(
				'name'			=> 'street_address',
				'itemprop'		=> 'streetAddress',
				'h_card_class'	=> 'street-address'
			),
			array(
				'name'			=> 'state',
				'itemprop'		=> 'addressRegion',
				'h_card_class'	=> 'region'
			),
			array(
				'name'			=> 'city',
				'itemprop'		=> 'addressLocality',
				'h_card_class'	=> 'locality'
			),
			array(
				'name'			=> 'postcode',
				'itemprop'		=> 'postalCode',
				'h_card_class'	=> 'postal-code'
			),
			array(
				'name'			=> 'country',
				'itemprop'		=> 'addressCountry',
				'h_card_class'	=> 'country-name'
			)
		);
	}
	
	/**
	 * Returns the settings form values.
	 * 
	 * @deprecated since 1.1
	 * @since 1.0
	 * 
	 * @return array Form values.
	 */
	public function get_settings()
	{
		if (($form_values = get_option('lbc_local_seo_settings')) == false) {
			update_option('lbc_local_seo_settings', $this->_default_settings_form_values);
			$form_values = $this->_default_settings_form_values;
		}
		
		if (array_keys($form_values) != array_keys($this->_default_settings_form_values)) {
			$tmp_arr = $this->_default_settings_form_values;
			foreach ($form_values as $field_key => $field_value) {
				if (isset($tmp_arr[$field_key]) && $tmp_arr[$field_key] != $field_value) {
					$tmp_arr[$field_key] = $field_value;
				}
			}
			$form_values = $tmp_arr;
		}
			
		return $form_values;
	}
	
	/**
	 * Returns the location settings form values.
	 *
	 * @since 1.0
	 *
	 * @return array Form values.
	 */
	public function get_location_settings()
	{
		global $post;
		
		if (($form_values = get_option('lbc_local_seo_location_settings')) == false) {
			update_option('lbc_local_seo_location_settings', $this->_default_location_settings_form_values);
			$form_values = $this->_default_location_settings_form_values;
		}
	
		if (array_keys($form_values) != array_keys($this->_default_location_settings_form_values)) {
			$tmp_arr = $this->_default_location_settings_form_values;
			foreach ($form_values as $field_key => $field_value) {
				if (isset($tmp_arr[$field_key]) && $tmp_arr[$field_key] != $field_value) {
					$tmp_arr[$field_key] = $field_value;
				}
			}
			$form_values = $tmp_arr;
		}
		
		if (isset($form_values['spec_opening_hours'])) {
			ksort($form_values['spec_opening_hours']);
		}
		
		$to_strip_slashes = array(
			'name', 'short_desc', 'meta_desc', 'street_address', 'city', 'state', 'postcode',
			'accepted_payment_type'
		);
		foreach ($to_strip_slashes as $tss) {
			$form_values[$tss] = stripslashes($form_values[$tss]);
		}
		
		foreach ($form_values['spec_opening_hours'] as $soh_date => &$soh_data) {
			if (isset($soh_data['title'])) {
				$soh_data['title'] = stripslashes($soh_data['title']);
			}
		}
		
		$form_values['read_more_url'] = get_permalink($post->ID);
			
		return $form_values;
	}
	
	/**
	 * Returns if a given business type is valid or not.
	 * 
	 * @param string $type Business type.
	 * 
	 * @since 1.0
	 * 
	 * @return boolean If valid then true, otherwise false.
	 */
	private function _is_valid_business_type($type)
	{
		$business_types = $this->get_business_types();
		
		foreach ($business_types as $b_type_id => $b_type_data) {
			if ($b_type_id == $type) {
				return true;
			}
			if (isset($b_type_data['subtypes'])) {
				foreach ($b_type_data['subtypes'] as $b_subtype_id => $b_subtype_title) {
					if ($b_subtype_id == $type) {
						return true;
					}
				}
			}
		}
		
		return false;
	}
	
	/**
	 * Checks if $a or $b is a larger day.
	 * Callback function for uksort.
	 * 
	 * @param string $a 3-letter day name. (Values: 'mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun')
	 * @param string $b 3-letter day name. (Values: 'mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun')
	 * 
	 * @since 1.0
	 * 
	 * @return int
	 */
	public function sort_by_weekdays($a, $b)
	{
		$weekdays = array('mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun');
		
		return array_search(strtolower($a), $weekdays) - array_search(strtolower($b), $weekdays);
	}
	
	/**
	 * Returns the sanitised data of the settings form. 
	 * 
	 * @param array $post Posted variables.
	 * 
	 * @since 1.0
	 * 
	 * @return array Sanitised data.
	 */
	private function _get_sanitized_settings($post)
	{
		$ret_arr = $this->_default_settings_form_values;
		
		if (isset($post['vars'])) {
			parse_str($post['vars'], $post['vars']);
			
			if (isset($post['vars']['lbc_var_local_seo_address_format'])) {
				$ret_arr['addr_format'] = sanitize_text_field($post['vars']['lbc_var_local_seo_address_format']);
			}
			
			if (isset($post['vars']['lbc_var_local_seo_location_page_slug'])) {
				$ret_arr['location_page_slug'] = $post['vars']['lbc_var_local_seo_location_page_slug'];
				if (preg_match($this->_slug_regexp, $post['vars']['lbc_var_local_seo_location_page_slug']) != 1) {
					$ret_arr['location_page_slug'] = strtolower($ret_arr['location_page_slug']);
					$ret_arr['location_page_slug'] = preg_replace('/[^a-z0-9\-]/', '-', $ret_arr['location_page_slug']);
					$ret_arr['location_page_slug'] = preg_replace('/[-]{2,}/', '-', $ret_arr['location_page_slug']);
					$ret_arr['location_page_slug'] = (substr($ret_arr['location_page_slug'], 0, 1) == '-') ? substr($ret_arr['location_page_slug'], 1) : $ret_arr['location_page_slug'];
					$ret_arr['location_page_slug'] = (substr($ret_arr['location_page_slug'], -1) == '-') ? substr($ret_arr['location_page_slug'], 0, -1) : $ret_arr['location_page_slug'];
					$ret_arr['location_page_slug'] = trim($ret_arr['location_page_slug']);
				}
			}
			
			if (isset($post['vars']['lbc_var_local_seo_google_maps_api_key'])) {
				$ret_arr['google_maps_api_key'] = sanitize_text_field($post['vars']['lbc_var_local_seo_google_maps_api_key']);
			}
			
			if (isset($post['vars']['lbc_var_local_seo_opening_hours_format'])) {
				$ret_arr['opening_hours_format'] = (int)$post['vars']['lbc_var_local_seo_opening_hours_format'];
			}
		}
		
		return $ret_arr;
	}
	
	/**
	 * Returns the sanitised data of the location settings form.
	 *
	 * @param array $post Posted variables.
	 *
	 * @since 1.0
	 *
	 * @return array Sanitised data.
	 */
	private function _get_sanitized_location_settings($post)
	{
		$ret_arr = $this->_default_location_settings_form_values;
	
		if (isset($post['vars'])) {
			parse_str($post['vars'], $post['vars']);
			
			if (isset($post['vars']['lbc_var_local_seo_business_name'])) {
				$ret_arr['name'] = sanitize_text_field($post['vars']['lbc_var_local_seo_business_name']);
			}
				
			if (isset($post['vars']['lbc_var_local_seo_business_type'])) {
				if ($this->_is_valid_business_type($post['vars']['lbc_var_local_seo_business_type']) === true) {
					$ret_arr['type'] = $post['vars']['lbc_var_local_seo_business_type'];
				}
				else {
					$ret_arr['type'] = 'LocalBusiness';
				}
			}
				
			if (isset($post['vars']['lbc_var_local_seo_business_short_desc'])) {
				$ret_arr['short_desc'] = esc_html($post['vars']['lbc_var_local_seo_business_short_desc']);
			}
			
			if (isset($post['vars']['lbc_var_local_seo_business_meta_desc']) &&
				$post['vars']['lbc_var_local_seo_business_meta_desc'] != '')
			{
				$ret_arr['meta_desc'] = $this->_convert_to_single_line(
					sanitize_text_field($post['vars']['lbc_var_local_seo_business_meta_desc'])
				);
			}
			elseif (isset($post['vars']['lbc_var_local_seo_business_short_desc']) &&
					$post['vars']['lbc_var_local_seo_business_short_desc'] != '')
			{
				$ret_arr['meta_desc'] = $this->_convert_to_single_line(
					sanitize_text_field($post['vars']['lbc_var_local_seo_business_short_desc'])
				);
			
				$ret_arr['meta_desc'] = $this->_shorten_str($ret_arr['meta_desc'], LBC_LS_META_DESC_MAX_LEN);
			}
			else {
				$ret_arr['meta_desc'] = '{LBC_LS_LOCATION_META_DESC}';
			}
			
			if (isset($post['vars']['lbc_var_local_seo_business_website_url'])) {
				$ret_arr['url'] = esc_url($post['vars']['lbc_var_local_seo_business_website_url']);
			}
			
			if (isset($post['vars']['lbc_var_local_seo_business_logo'])) {
				$ret_arr['logo'] = esc_url($post['vars']['lbc_var_local_seo_business_logo']);
			}
			
			if (isset($post['vars']['lbc_var_local_seo_business_street_address'])) {
				$ret_arr['street_address'] = sanitize_text_field($post['vars']['lbc_var_local_seo_business_street_address']);
			}
			
			if (isset($post['vars']['lbc_var_local_seo_business_city'])) {
				$ret_arr['city'] = sanitize_text_field($post['vars']['lbc_var_local_seo_business_city']);
			}
			
			if (isset($post['vars']['lbc_var_local_seo_business_state'])) {
				$ret_arr['state'] = sanitize_text_field($post['vars']['lbc_var_local_seo_business_state']);
			}
			
			if (isset($post['vars']['lbc_var_local_seo_business_country'])) {
				$countries = $this->get_countries();
			
				if (isset($countries[ $post['vars']['lbc_var_local_seo_business_country'] ])) {
					$ret_arr['country'] = $post['vars']['lbc_var_local_seo_business_country'];
				}
				else {
					$ret_arr['country'] = '';
				}
			}
			
			if (isset($post['vars']['lbc_var_local_seo_business_postcode'])) {
				$ret_arr['postcode'] = sanitize_text_field($post['vars']['lbc_var_local_seo_business_postcode']);
			}
			
			if (isset($post['spec_vars']['show_on_location_page']['map']) &&
				$post['spec_vars']['show_on_location_page']['map'] == 1)
			{
				$ret_arr['on_location_page_map'] = 1;
			}
			else {
				$ret_arr['on_location_page_map'] = 0;
			}
			
			if (isset($post['vars']['lbc_var_local_seo_business_latitude']) &&
				preg_match($this->_coordinate_regexp, $post['vars']['lbc_var_local_seo_business_latitude']) == 1)
			{
				$ret_arr['latitude'] = round($post['vars']['lbc_var_local_seo_business_latitude'], 8);
			}
			
			if (isset($post['vars']['lbc_var_local_seo_business_longitude']) &&
				preg_match($this->_coordinate_regexp, $post['vars']['lbc_var_local_seo_business_longitude']) == 1)
			{
				$ret_arr['longitude'] = round($post['vars']['lbc_var_local_seo_business_longitude'], 8);
			}
			
			if (isset($post['spec_vars']['show_on_location_page']['contacts']) &&
				$post['spec_vars']['show_on_location_page']['contacts'] == 1)
			{
				$ret_arr['on_location_page_contacts'] = 1;
			}
			else {
				$ret_arr['on_location_page_contacts'] = 0;
			}
			
			foreach (array('phone', 'fax') as $type) {
				if (isset($post['vars']['lbc_var_local_seo_business_' . $type . '_numbers']) &&
					is_array($post['vars']['lbc_var_local_seo_business_' . $type . '_numbers']))
				{
					$ret_arr[$type . '_numbers'] = array();
					foreach ($post['vars']['lbc_var_local_seo_business_' . $type . '_numbers'] as $num) {
						if (($sanitized_num = sanitize_text_field($num)) != '') {
							$ret_arr[$type . '_numbers'][] = $sanitized_num;
						}
					}
				}
			}
			
			if (isset($post['vars']['lbc_var_local_seo_business_email'])) {
				$ret_arr['email'] = sanitize_email($post['vars']['lbc_var_local_seo_business_email']);
			}
			
			if (isset($post['spec_vars']['show_on_location_page']['payments']) &&
				$post['spec_vars']['show_on_location_page']['payments'] == 1)
			{
				$ret_arr['on_location_page_payments'] = 1;
			}
			else {
				$ret_arr['on_location_page_payments'] = 0;
			}
			
			if (isset($post['vars']['lbc_var_local_seo_accepted_payment_type'])) {
				$ret_arr['accepted_payment_type'] = sanitize_text_field($post['vars']['lbc_var_local_seo_accepted_payment_type']);
			}
			
			if (isset($post['vars']['lbc_var_local_seo_accepted_currencies_str']) &&
				preg_match('/^[A-Z]{3}(, [A-Z]{3})*$/', $post['vars']['lbc_var_local_seo_accepted_currencies_str']) == 1)
			{
				$ret_arr['accepted_currencies'] = sanitize_text_field($post['vars']['lbc_var_local_seo_accepted_currencies_str']);
			}
			
			if (isset($post['vars']['lbc_var_local_seo_price_range']) &&
				preg_match('/^[$]{0,5}$/', $post['vars']['lbc_var_local_seo_price_range']) == 1)
			{
				$ret_arr['price_range'] = sanitize_text_field($post['vars']['lbc_var_local_seo_price_range']);
			}
			
			if (isset($post['spec_vars']['show_on_location_page']['opening_hours']) &&
				$post['spec_vars']['show_on_location_page']['opening_hours'] == 1)
			{
				$ret_arr['on_location_page_opening_hours'] = 1;
			}
			else {
				$ret_arr['on_location_page_opening_hours'] = 0;
			}
			
			// opening hours
			if (isset($post['spec_vars']['opening_hours'])) {
				$ret_arr['opening_hours'] = array();
				
				foreach ($post['spec_vars']['opening_hours'] as $oh_day => $oh_data) {
					if (isset($this->_days[$oh_day])) {
						foreach (array('1st', '2nd') as $first_second) {
							if (isset($oh_data[$first_second . '_open'])) {
								$oh_data[$first_second . '_open'] = (int)$oh_data[$first_second . '_open'];
								
								if ($oh_data[$first_second . '_open'] == 1) {
									// day
									if (!isset($ret_arr['opening_hours'][$oh_day])) {
										$ret_arr['opening_hours'][$oh_day] = array();
									}
									
									// open
									$ret_arr['opening_hours'][$oh_day][$first_second . '_open'] = 1; // based on the condition above it can't be anything else
									
									// opens/closes at
									foreach (array('opens', 'closes') as $open_close) {
										if (isset($oh_data[$first_second . '_' . $open_close . '_at'])) {
											if (preg_match($this->_time_format_24_regexp, $oh_data[$first_second . '_' . $open_close . '_at']) == 1) {
												$ret_arr['opening_hours'][$oh_day][$first_second . '_' . $open_close . '_at'] = $oh_data[$first_second . '_' . $open_close . '_at'];
											}
											elseif (preg_match($this->_time_format_12_regexp, $oh_data[$first_second . '_' . $open_close . '_at']) == 1 &&
													($ts = strtotime($oh_data[$first_second . '_' . $open_close . '_at'])) != false)
											{
												$ret_arr['opening_hours'][$oh_day][$first_second . '_' . $open_close . '_at'] = date('G:i', $ts);
											}
										}
									}
									
									// unset open param if opens at and closes at are not set
									if (!isset($ret_arr['opening_hours'][$oh_day][$first_second . '_opens_at']) &&
										!isset($ret_arr['opening_hours'][$oh_day][$first_second . '_closes_at']))
									{
										unset($ret_arr['opening_hours'][$oh_day][$first_second . '_open']);
									}
								}
							} 
						}
						
						// unset day if empty
						if (count($ret_arr['opening_hours'][$oh_day]) == 0) {
							unset($ret_arr['opening_hours'][$oh_day]);
						}
					}
				}
				
				// unset opening hours if empty
				if (count($ret_arr['opening_hours']) == 0) {
					unset($ret_arr['opening_hours']);
				}
				else {
					uksort($ret_arr['opening_hours'], array($this, 'sort_by_weekdays'));
				}
			}
			
			if (isset($post['spec_vars']['show_on_location_page']['spec_opening_hours']) &&
				$post['spec_vars']['show_on_location_page']['spec_opening_hours'] == 1)
			{
				$ret_arr['on_location_page_spec_opening_hours'] = 1;
			}
			else {
				$ret_arr['on_location_page_spec_opening_hours'] = 0;
			}
			
			// spec opening hours
			if (isset($post['spec_vars']['spec_opening_hours'])) {
				$ret_arr['spec_opening_hours'] = array();
			
				foreach ($post['spec_vars']['spec_opening_hours'] as $soh_date => $soh_data) {
					if ($this->_is_valid_date_iso8601($soh_date) === true) {
						if (isset($soh_data['closed_all_day'])) {
							$soh_data['closed_all_day'] = (int)$soh_data['closed_all_day'];
							
							if ($soh_data['closed_all_day'] == 1) {
								// date
								if (!isset($ret_arr['spec_opening_hours'][$soh_date])) {
									$ret_arr['spec_opening_hours'][$soh_date] = array();
								}
								
								// title
								if (isset($soh_data['title']) && $soh_data['title'] != '') {
									$ret_arr['spec_opening_hours'][$soh_date]['title'] = sanitize_text_field($soh_data['title']);
								}
								
								// closed all day
								$ret_arr['spec_opening_hours'][$soh_date]['closed_all_day'] = 1; // based on the condition above it can't be anything else
							}
						}
						else {
							foreach (array('1st', '2nd') as $first_second) {
								if (isset($soh_data[$first_second . '_open'])) {
									$soh_data[$first_second . '_open'] = (int)$soh_data[$first_second . '_open'];
										
									if ($soh_data[$first_second . '_open'] == 1) {
										// date
										if (!isset($ret_arr['spec_opening_hours'][$soh_date])) {
											$ret_arr['spec_opening_hours'][$soh_date] = array();
										}
										
										// title
										if (isset($soh_data['title']) && $soh_data['title'] != '') {
											$ret_arr['spec_opening_hours'][$soh_date]['title'] = sanitize_text_field($soh_data['title']);
										}
					
										// open
										$ret_arr['spec_opening_hours'][$soh_date][$first_second . '_open'] = 1; // based on the condition above it can't be anything else
					
										// opens/closes at
										foreach (array('opens', 'closes') as $open_close) {
											if (isset($soh_data[$first_second . '_' . $open_close . '_at'])) {
												if (preg_match($this->_time_format_24_regexp, $soh_data[$first_second . '_' . $open_close . '_at']) == 1) {
													$ret_arr['spec_opening_hours'][$soh_date][$first_second . '_' . $open_close . '_at'] = $soh_data[$first_second . '_' . $open_close . '_at'];
												}
												elseif (preg_match($this->_time_format_12_regexp, $soh_data[$first_second . '_' . $open_close . '_at']) == 1 &&
														($ts = strtotime($soh_data[$first_second . '_' . $open_close . '_at'])) != false)
												{
													$ret_arr['spec_opening_hours'][$soh_date][$first_second . '_' . $open_close . '_at'] = date('G:i', $ts);
												}
											}
										}
					
										// unset open param if opens at and closes at are not set
										if (!isset($ret_arr['spec_opening_hours'][$soh_date][$first_second . '_opens_at']) &&
											!isset($ret_arr['spec_opening_hours'][$soh_date][$first_second . '_closes_at']))
										{
											unset($ret_arr['spec_opening_hours'][$soh_date][$first_second . '_open']);
										}
									}
								}
							}
						}
						
						// unset date if empty
						if (count($ret_arr['spec_opening_hours'][$soh_date]) == 0) {
							unset($ret_arr['spec_opening_hours'][$soh_date]);
						}
					}
				}
			
				// unset opening hours if empty
				if (count($ret_arr['spec_opening_hours']) == 0) {
					unset($ret_arr['spec_opening_hours']);
				}
				else {
					uksort($ret_arr['spec_opening_hours'], array($this, 'sort_by_weekdays'));
				}
			}
			
			if (isset($post['spec_vars']['show_on_location_page']['social_links']) &&
				$post['spec_vars']['show_on_location_page']['social_links'] == 1)
			{
				$ret_arr['on_location_page_social_links'] = 1;
			}
			else {
				$ret_arr['on_location_page_social_links'] = 0;
			}
			
			// social links
			$social_icons = $this->get_social_icons();
			foreach ($social_icons as $si_id => $si_data) {
				if (isset($post['vars']['lbc_var_local_seo_social_link_' . $si_id])) {
					$ret_arr['social_link_' . $si_id] = esc_url($post['vars']['lbc_var_local_seo_social_link_' . $si_id]);
				}
			}
			unset($social_icons);
			
			// save date
			$ret_arr['save_date'] = date('Y-m-d H:i:s');
		}
	
		return $ret_arr;
	}
	
	/**
	 * Saves default settings.
	 * 
	 * @since 1.1.2
	 * 
	 * @return boolean True on succcessful save, otherwise false.
	 */
	public function save_default_settings()
	{
		return update_option('lbc_local_seo_settings', $this->_default_settings_form_values);
	}
	
	/**
	 * Saves the settings. The settings are sanitized.
	 * 
	 * @param array $post
	 * 
	 * @since 1.0
	 * 
	 * @return boolean True on succcessful save, otherwise false.
	 */
	public function save_settings($post)
	{
		$sanitized_settings = $this->_get_sanitized_settings($post);
		
		if (($settings_in_db = get_option('lbc_local_seo_settings')) !== false) {
			// update location page
			if (isset($settings_in_db['location_page_slug']) &&
				isset($sanitized_settings['location_page_slug']))
			{
				$sanitized_settings['location_page_slug'] = $this->_update_location_page(
					$settings_in_db['location_page_slug'],
					$sanitized_settings['location_page_slug']
				);
			}
			
			if (serialize($settings_in_db) == serialize($sanitized_settings)) {
				return true; // if we don't update the db then return true
			}
			
			return update_option('lbc_local_seo_settings', $sanitized_settings);
		}
		
		return false;
	}
	
	/**
	 * Updates the location page.
	 * WP may modify the slug during the process. Even in that case the new slug is returned.
	 * 
	 * @param string $original_slug
	 * @param string $new_slug
	 * 
	 * @since 1.0
	 * 
	 * @return string New location page slug.
	 */
	private function _update_location_page($original_slug, $new_slug)
	{
		$page = get_posts(array(
			'pagename'		=> $original_slug,
			'post_type'		=> 'page'
		));
		if ($page && $original_slug != $new_slug) {
			$page_id = wp_update_post(array(
				'ID'			=> $page[0]->ID,
				'post_name'		=> $new_slug
			));
		}
		else if ($page && $original_slug == $new_slug) {
			return $page[0]->post_name;
		}
		else {
			$page_id = wp_insert_post(array(
				'post_name'			=> $new_slug,
				'post_type'			=> 'page',
				'post_title'		=> __('Location', 'lbc-local-seo'),
				'post_content'		=> '',
				'post_status'		=> 'publish',
				'comment_status'	=> 'closed',
				'ping_status'		=> 'closed'
			));
		}
		
		/*
		 * Get the slug from the DB and return it.
		 * This step is required because the WP appends the slug with a number
		 * if the slug we want to create already exists in the the DB.
		 * E.g.:
		 *   The 'location' slug exists in the DB.
		 *   If we want to add it again the WP will change the slug to 'location-{n}' ({n} is a number. {n} >= 2).
		 */
		$page = get_posts(array(
			'page_id' => $page_id,
			'post_type'	=> 'page'
		));		
		if ($page) {
			return $page[0]->post_name;
		}
		
		return $this->_default_settings_form_values['location_page_slug'];
	}
	
	/**
	 * Saves the location settings. The location settings are sanitized.
	 *
	 * @param array $post
	 *
	 * @since 1.0
	 *
	 * @return boolean True on succcessful save, otherwise false.
	 */
	public function save_location_settings($post)
	{
		$sanitized_settings = $this->_get_sanitized_location_settings($post);
		
		if (($settings_in_db = get_option('lbc_local_seo_location_settings')) !== false) {
			if (serialize($settings_in_db) == serialize($sanitized_settings)) {
				return true; // if we don't update the db then return true
			}
				
			return update_option('lbc_local_seo_location_settings', $sanitized_settings);
		}
	
		return false;
	}
	
	/**
	 * Returns the given HTML string wrapped in an info comment.
	 *
	 * @param string $html
	 *
	 * @since 1.1
	 *
	 * @return string HTML wrapped in info comment.
	 */
	private function _wrap_html_into_info_comment($html)
	{
		return '<!-- BEGIN Added by LBC Local SEO v' . esc_html(LBC_LS_PLUGIN_VERSION) . ' WordPress plugin -->' .
				$html .
				'<!-- END Added by LBC Local SEO v' . esc_html(LBC_LS_PLUGIN_VERSION) . ' WordPress plugin -->';
	}
	
	/**
	 * Echos meta tags for a single location page.
	 * 
	 * @since 1.1
	 */
	public function echo_location_page_meta_html()
	{
		$settings = $this->get_settings();
		
		// location settings
		$location_settings = $this->get_location_settings();
		
		if (count($location_settings) > 0 && $location_settings['meta_desc'] == '{LBC_LS_LOCATION_META_DESC}') {
			// address
			if (isset($location_settings['street_address']) && $location_settings['street_address'] != '' &&
				isset($location_settings['city']) && $location_settings['city'] != '' &&
				isset($location_settings['postcode']) && $location_settings['postcode'] != '' &&
				isset($location_settings['country']) && $location_settings['country'] != '')
			{
				$addr = str_replace(
					array('{street address}', '{region}', '{state}', '{city}', '{postcode}', '{zipcode}', '{country}', ' / '),
					array(
						$location_settings['street_address'],
						$location_settings['state'],
						$location_settings['state'],
						$location_settings['city'],
						$location_settings['postcode'],
						$location_settings['postcode'],
						$location_settings['country'],
						', '
					),
					$settings['addr_format']
				);
				
				$addr = __('Address') . ': ' .  preg_replace('/( ){2,}/', ' ', preg_replace('/(, ){2,}/', ', ', $addr));
			}
			else {
				$addr = '';
			}
			
			$addr_len = strlen($addr);
			
			// phone numbers
			if (count($location_settings['phone_numbers']) > 0 && $addr_len < LBC_LS_META_DESC_MAX_LEN) {
				$phone = __('Phone numbers', 'lbc-local-seo') . ': ' . implode(', ', $location_settings['phone_numbers']);
			}
			else {
				$phone = '';
			}
			
			// meta desc
			if ($addr != '' || $phone != '') {
				$meta_desc = $this->_shorten_str($addr . (($addr != '' && $phone != '') ? ' | ' : '') . $phone, LBC_LS_META_DESC_MAX_LEN);
				
				$meta_desc_len = strlen($meta_desc);
				$meta_desc = (($meta_desc[$meta_desc_len - 2] . $meta_desc[$meta_desc_len - 1]) == ' |') ? trim(substr($meta_desc, 0, -2)) : $meta_desc;
			}
			else {
				$meta_desc = $location_settings['name'] . ' - ' . __('A location where you can find us.', 'lbc-local-seo');
			}
		}
		else {
			$meta_desc = $location_settings['meta_desc'];
		}
		
		$meta_html = '';
	
		// meta description
		$meta_html .= '<meta name="description" content="' . esc_attr($meta_desc) . '" />';
		
		echo $this->_wrap_html_into_info_comment($meta_html);
	}
	
	/**
	 * Returns advert details. It connects to a server and requests the details.
	 * 
	 * @since 1.1
	 * 
	 * @return array
	 */
	public function get_adverts()
	{
		if (LBC_LS_ADVERT_SRC != '') {
			$params = array(
				'_GM-b8dbJdbXam4SZzwV'	=> 1,
				'source'				=> home_url()
			);
			
			$response = wp_remote_get(
				add_query_arg($params, trailingslashit(LBC_LS_ADVERT_SRC) . '33od2-20S/mIr6IQ-z.php'),
				array('timeout' => 5)
			);
			
			// make sure the response came back okay
			if (is_wp_error($response)) {
				return array();
			}
			
			return json_decode(wp_remote_retrieve_body($response), true);
		}
		
		return array();
	}
}