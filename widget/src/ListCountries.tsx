import React, { useState } from "react";
import _ from "lodash";
import McsList from "./McsList";
import { Grid } from "@material-ui/core";
import { McsData } from "./types/data";
import { handleLocationSelectFn } from "./McsWidget";

interface ListCountriesProps {
	data: McsData;
	onSelectCountry: handleLocationSelectFn;
}

export const ListCountries: React.FC<ListCountriesProps> = ({
	data,
	onSelectCountry,
}: ListCountriesProps) => {
	const countries = _.sortBy(data.countries, ["title"]);

	return (
		<Grid container>
			<Grid item xs={12} sm={4}>
				<McsList
					title="Country"
					handleItemClick={onSelectCountry}
					items={countries}
					prefix="mcs-country"
				/>
			</Grid>
		</Grid>
	);
};
