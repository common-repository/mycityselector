import ListItem from "@material-ui/core/ListItem";
import ListItemText from "@material-ui/core/ListItemText";
import { Box, Typography } from "@material-ui/core";
import React, { useState, memo, useMemo, useCallback } from "react";
import List from "@material-ui/core/List";
import _ from "lodash";
import { makeStyles } from "@material-ui/core/styles";
import { McsCity, McsCountry, McsLocation, McsProvince } from "./types/data";
import { handleLocationSelectFn } from "./McsWidget";

interface McsListItemProps {
	isSelected: boolean | undefined;
	onClick: handleLocationSelectFn;
	location: McsCity | McsProvince | McsCountry;
	title: string;
	prefix: string;
}
const McsListItem: React.FC<McsListItemProps> = ({
	isSelected,
	onClick,
	location,
	title,
	prefix,
}: McsListItemProps) => {
	const clickItem = useCallback(() => onClick(location), [location, onClick]);
	return (
		<Box borderLeft={isSelected ? 4 : 0} borderColor="primary.main">
			<ListItem button selected={isSelected} onClick={clickItem}>
				<ListItemText id={`${prefix}-${location.id}`} primary={title} />
			</ListItem>
		</Box>
	);
};

const McsListItemMemo = memo(McsListItem);

const useStyles = makeStyles(() => ({
	root: {
		overflow: "auto",
		maxHeight: "calc(100vh - 200px)",
		"&::-webkit-scrollbar": {
			width: "0.5rem",
		},
		"&::-webkit-scrollbar-track": {
			boxShadow: "inset 0 0 6px rgba(0,0,0,0.00)",
			webkitBoxShadow: "inset 0 0 6px rgba(0,0,0,0.00)",
		},
		"&::-webkit-scrollbar-thumb": {
			backgroundColor: "rgba(0,0,0,.1)",
		},
	},
}));

interface McsListProps {
	title?: string;
	items: McsLocation[];
	selectedIndex?: number;
	handleItemClick: handleLocationSelectFn;
	prefix: string;
	withCitySearch?: boolean;
}
const McsList: React.FC<McsListProps> = ({
	title,
	items,
	selectedIndex,
	handleItemClick,
	prefix,
	withCitySearch,
}: McsListProps) => {
	const [searchValue, setSearchValue] = useState("");
	const classes = useStyles();
	const filteredItems = useMemo<McsLocation[]>((): McsLocation[] => {
		if (searchValue) {
			const loweredSearchTitle = _.toLower(searchValue);
			return _.filter(items, (item: McsLocation) => {
				return _.includes(_.toLower(item.title), loweredSearchTitle);
			}) as McsLocation[];
		}
		return items;
	}, [items, searchValue]);

	const handleSearchInput = useCallback((e) => {
		setSearchValue(e.target.value);
	}, []);

	return (
		<>
			{!!title && <Typography variant="h6">{title}</Typography>}
			{withCitySearch && (
				<input
					type="search"
					className="search-field"
					placeholder="Search…"
					value={searchValue}
					onChange={handleSearchInput}
				/>
			)}
			<List component="nav" className={classes.root}>
				{filteredItems.map((item, index) => {
					const isSelected = selectedIndex === index;
					return (
						<McsListItemMemo
							key={_.get(item, "id")}
							location={item}
							onClick={handleItemClick}
							isSelected={isSelected}
							title={_.get(item, "title")}
							prefix={prefix}
						/>
					);
				})}
			</List>
		</>
	);
};

export default McsList;
