import React, { useState } from "react";
import _ from "lodash";
import McsList from "./McsList";
import { Grid } from "@material-ui/core";
import { McsData } from "./types/data";
import { handleLocationSelectFn } from "./McsWidget";

interface ListCountriesProvincesCitiesProps {
	data: McsData;
	onSelectCity: handleLocationSelectFn;
}

export const ListCountriesProvincesCities: React.FC<ListCountriesProvincesCitiesProps> =
	({ data, onSelectCity }: ListCountriesProvincesCitiesProps) => {
		const [selectedCountryIndex, setSelectedCountryIndex] = useState(0);
		const [selectedProvinceIndex, setSelectedProvinceIndex] = useState(0);
		const [selectedCityIndex, setSelectedCityIndex] = useState(undefined);

		const countries = _.sortBy(data.countries, ["title"]);
		const provinces = _.sortBy(
			_.filter(data.provinces, [
				"country_id",
				countries[selectedCountryIndex]?.id,
			]),
			["title"]
		);
		const cities = _.sortBy(
			_.filter(data.cities, (city) => {
				if (provinces[selectedProvinceIndex]?.id) {
					return (
						city.province_id ===
						provinces[selectedProvinceIndex]?.id
					);
				} else if (countries[selectedCountryIndex]?.id) {
					return (
						city.country_id === countries[selectedCountryIndex].id
					);
				}
				return false;
			}),
			["title"]
		);

		const handleCountryClick: handleLocationSelectFn = (country) => {
			const index = _.findIndex(countries, ["id", country.id]);
			if (selectedCountryIndex !== index) {
				setSelectedCountryIndex(index);
				setSelectedProvinceIndex(0);
				setSelectedCityIndex(undefined);
			}
		};
		const handleProvinceClick: handleLocationSelectFn = (province) => {
			const index = _.findIndex(provinces, ["id", province.id]);
			if (selectedProvinceIndex !== index) {
				setSelectedProvinceIndex(index);
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
						title="State / Province"
						handleItemClick={handleProvinceClick}
						items={provinces}
						selectedIndex={selectedProvinceIndex}
						prefix="mcs-province"
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
