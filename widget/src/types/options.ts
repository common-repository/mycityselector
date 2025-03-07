import {
	LIST_MODE_CITIES,
	LIST_MODE_COUNTRIES,
	LIST_MODE_COUNTRIES_CITIES,
	LIST_MODE_COUNTRIES_PROVINCES_CITIES,
	LIST_MODE_PROVINCES_CITIES,
	LOCATION_TYPE_CITY,
	LOCATION_TYPE_COUNTRY,
	LOCATION_TYPE_PROVINCE,
	SEO_MODE_COOKIE,
	SEO_MODE_SUBDOMAIN,
	SEO_MODE_SUBFOLDER,
} from "./constants";

export interface McsOptions {
	title: string;
	list_mode:
		| typeof LIST_MODE_CITIES
		| typeof LIST_MODE_PROVINCES_CITIES
		| typeof LIST_MODE_COUNTRIES_PROVINCES_CITIES
		| typeof LIST_MODE_COUNTRIES_CITIES
		| typeof LIST_MODE_COUNTRIES;
	seo_mode:
		| typeof SEO_MODE_COOKIE
		| typeof SEO_MODE_SUBDOMAIN
		| typeof SEO_MODE_SUBFOLDER;
	default_location_id: number | null;
	default_location_type:
		| typeof LOCATION_TYPE_CITY
		| typeof LOCATION_TYPE_PROVINCE
		| typeof LOCATION_TYPE_COUNTRY
		| null;
	base_domain: string;
}
