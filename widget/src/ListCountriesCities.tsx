import React, { useState } from "react";
import _ from "lodash";
import McsList from "./McsList";
import { Grid } from "@material-ui/core";
import { McsData } from "./types/data";
import { handleLocationSelectFn } from "./McsWidget";

interface ListCountriesCitiesProps {
	data: McsData;
	onSelectCity: handleLocationSelectFn;
}

export const ListCountriesCities: React.FC<ListCountriesCitiesProps> = ({
	data,
	onSelectCity,
}: ListCountriesCitiesProps) => {
	const [selectedCountryIndex, setSelectedCountryIndex] = useState(0);
	const [selectedCityIndex, setSelectedCityIndex] = useState(undefined);

	const countries = _.sortBy(data.countries, ["title"]);

	const cities = _.sortBy(
		_.filter(data.cities, (city) => {
			if (countries[selectedCountryIndex]?.id) {
				return city.country_id === countries[selectedCountryIndex].id;
			}
			return false;
		}),
		["title"]
	);

	const handleCountryClick: handleLocationSelectFn = (country) => {
		const index = _.findIndex(countries, ["id", country.id]);
		if (selectedCountryIndex !== index) {
			setSelectedCountryIndex(index);
			setSelectedCityIndex(undefined);
		}
	};

	return (
		<Grid container>
			<Grid item xs={12} sm={4}>
				<McsList
					title="Country"
					handleItemClick={handleCountryClick}
					items={countries}
					selectedIndex={selectedCountryIndex}
					prefix="mcs-country"
				/>
			</Grid>
			<Grid item xs={12} sm={4}>
				<McsList
					title="City"
					handleItemClick={onSelectCity}
					items={cities}
					selectedIndex={selectedCityIndex}
					prefix="mcs-city"
				/>
			</Grid>
		</Grid>
	);
};
