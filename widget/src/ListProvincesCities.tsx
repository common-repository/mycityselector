import React, { useState } from "react";
import _ from "lodash";
import McsList from "./McsList";
import { Grid } from "@material-ui/core";
import { McsData } from "./types/data";
import { handleLocationSelectFn } from "./McsWidget";

interface ListProvincesCitiesProps {
	data: McsData;
	onSelectCity: handleLocationSelectFn;
}

export const ListProvincesCities: React.FC<ListProvincesCitiesProps> = ({
	data,
	onSelectCity,
}: ListProvincesCitiesProps) => {
	const [selectedProvinceIndex, setSelectedProvinceIndex] = useState(0);
	const [selectedCityIndex, setSelectedCityIndex] = useState(undefined);

	const provinces = _.sortBy(data.provinces, ["title"]);
	const cities = _.sortBy(
		_.filter(data.cities, (city) => {
			if (provinces[selectedProvinceIndex]?.id) {
				return (
					city.province_id === provinces[selectedProvinceIndex]?.id
				);
			}
			return false;
		}),
		["title"]
	);

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
