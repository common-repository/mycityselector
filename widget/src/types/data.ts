import {
	LOCATION_TYPE_CITY,
	LOCATION_TYPE_COUNTRY,
	LOCATION_TYPE_PROVINCE,
} from "./constants";

export interface McsCountry {
	id: number;
	title: string;
	subdomain: string;
	published: number | boolean;
	ordering: number;
	code: string;
	domain: string | null;
	default_city_id: number | null;
}

export interface McsProvince {
	id: number;
	title: string;
	country_id: number;
	subdomain: string;
	published: number | boolean;
	ordering: number;
}

export interface McsCity {
	id: number;
	title: string;
	country_id: number;
	province_id: number;
	subdomain: string;
	published: number | boolean;
	ordering: number;
}

export interface McsData {
	countries: { [key: number]: McsCountry };
	provinces: { [key: number]: McsProvince };
	cities: { [key: number]: McsCity };
	current_location_id: number;
	current_location_type: McsLocationType;
}

export type McsLocation = McsCity | McsProvince | McsCountry;
export type McsLocationType =
	| typeof LOCATION_TYPE_CITY
	| typeof LOCATION_TYPE_PROVINCE
	| typeof LOCATION_TYPE_COUNTRY;
