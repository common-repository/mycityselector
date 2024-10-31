import React, { useMemo } from "react";
import _ from "lodash";
import McsList from "./McsList";
import { McsCity, McsData } from "./types/data";
import { handleLocationSelectFn } from "./McsWidget";

interface ListCitiesProps {
	data: McsData;
	onSelectCity: handleLocationSelectFn;
	selectedCityIndex?: number;
}
export const ListCities: React.FC<ListCitiesProps> = ({
	data,
	onSelectCity,
	selectedCityIndex,
}: ListCitiesProps) => {
	const cities: McsCity[] = useMemo(
		() => _.sortBy(data.cities, ["title"]),
		[data]
	);
	// console.log(cities);

	return (
		<McsList
			withCitySearch
			handleItemClick={onSelectCity}
			items={cities}
			selectedIndex={selectedCityIndex}
			prefix="mcs-city"
			// onSearchInput={() => setSelectedCityIndex(null)}
		/>
	);
};
