import React, { useCallback, useState } from "react";
import Dialog from "@material-ui/core/Dialog";
import DialogContent from "@material-ui/core/DialogContent";
import MuiDialogTitle from "@material-ui/core/DialogTitle";
import {
	Box,
	createMuiTheme,
	IconButton,
	Link,
	Theme,
	Typography,
	withStyles,
} from "@material-ui/core";
import CloseIcon from "@material-ui/icons/Close";
import { ListCountriesProvincesCities } from "./ListCountriesProvincesCities";
import { makeStyles, ThemeProvider } from "@material-ui/core/styles";
import { ListCities } from "./ListCities";
import {
	COOKIE_DISABLE_POPUP,
	COOKIE_LOCATION_ID,
	COOKIE_LOCATION_TYPE,
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
} from "./types/constants";
import _ from "lodash";
import { McsOptions } from "./types/options";
import { McsData, McsLocation, McsLocationType } from "./types/data";
import { useCookies } from "react-cookie";
import { ClassNameMap } from "@material-ui/styles/withStyles/withStyles";
import { McsPopup } from "./McsPopup";
import { ListProvincesCities } from "./ListProvincesCities";
import { ListCountriesCities } from "./ListCountriesCities";
import { ListCountries } from "./ListCountries";

export type handleLocationSelectFn = (location: McsLocation) => void;
export type handleLocationAndTypeSelectFn = (
	location: McsLocation,
	mcsLocationConstType: McsLocationType
) => void;

export const McsTheme = createMuiTheme({
	typography: {
		htmlFontSize: 10,
	},
});

const styles = (theme: Theme) => ({
	root: {
		margin: 0,
		padding: theme.spacing(2),
	},
	closeButton: {
		position: "absolute" as const,
		right: theme.spacing(1),
		top: theme.spacing(1),
		color: theme.palette.grey[500],
	},
});

export const DialogTitle = withStyles(styles)(
	({
		children,
		classes,
		onClose,
		...other
	}: {
		children: React.Component | string;
		classes: ClassNameMap;
		onClose: () => void;
	}) => {
		return (
			<MuiDialogTitle
				id="scroll-dialog-title"
				disableTypography
				className={classes.root}
				{...other}
			>
				<Box mr={6}>
					<Typography variant="h6">{children}</Typography>
				</Box>

				{onClose ? (
					<IconButton
						aria-label="close"
						className={classes.closeButton}
						onClick={onClose}
					>
						<CloseIcon />
					</IconButton>
				) : null}
			</MuiDialogTitle>
		);
	}
);

const useStyles = makeStyles(() => ({
	root: {
		overflowY: "visible",
	},
	paper: {
		overflowY: "visible",
	},
}));

interface McsWidgetProps {
	options: McsOptions;
	data: McsData;
}

export const McsWidget: React.FC<McsWidgetProps> = ({
	options,
	data,
}: McsWidgetProps) => {
	console.log("options", options);
	console.log("data", data);

	const [locationTypeCookie, setLocationTypeCookie] = useCookies<
		typeof COOKIE_LOCATION_TYPE
	>([COOKIE_LOCATION_TYPE]);
	const [locationIdCookie, setLocationIdCookie] = useCookies<
		typeof COOKIE_LOCATION_ID
	>([COOKIE_LOCATION_ID]);
	const [cookieDisablePopup, setCookieDisablePopup] = useCookies<
		typeof COOKIE_DISABLE_POPUP
	>([COOKIE_DISABLE_POPUP]);

	const cookieLocationType: McsLocationType | undefined = _.get(
		locationTypeCookie,
		COOKIE_LOCATION_TYPE
	);

	const cookieLocationId: number | undefined = _.get(
		locationIdCookie,
		COOKIE_LOCATION_ID
	);

	const [open, setOpen] = useState(false);
	const [showPopup, setShowPopup] = useState(
		_.get(cookieDisablePopup, COOKIE_DISABLE_POPUP) != "1"
	);
	// console.log("locationType", locationType);
	// console.log("locationId", locationId);
	// console.log(
	// 	"_.get(cookieDisablePopup, COOKIE_DISABLE_POPUP)",
	// 	_.get(cookieDisablePopup, COOKIE_DISABLE_POPUP)
	// );
	// console.log("showPopup", showPopup);

	const [selectedCityId, setSelectedCityId] = useState<number | undefined>();
	const linkRef = React.useRef();

	let defaultLocation: McsLocation =
		data.countries[Object.keys(data.countries)[0]];
	let defaultLocationType: McsLocationType = LOCATION_TYPE_COUNTRY;

	if (options.default_location_id && options.default_location_type) {
		switch (options.default_location_type) {
			case LOCATION_TYPE_CITY:
				defaultLocation = data.cities[options.default_location_id];
				defaultLocationType = LOCATION_TYPE_CITY;
				break;
			case LOCATION_TYPE_PROVINCE:
				defaultLocation = data.provinces[options.default_location_id];
				defaultLocationType = LOCATION_TYPE_PROVINCE;
				break;
			case LOCATION_TYPE_COUNTRY:
				defaultLocation = data.countries[options.default_location_id];
				defaultLocationType = LOCATION_TYPE_COUNTRY;
				break;
		}
	}

	const handleClose = () => {
		setOpen(false);
	};

	const handleShowDialog: () => void = () => {
		setShowPopup(false);
		setOpen(true);
		setCookieDisablePopup(COOKIE_DISABLE_POPUP, "1", {
			domain: `.${options.base_domain}`,
		});
	};

	const handleLinkClick = (e) => {
		if (e) {
			e.preventDefault();
		}
		handleShowDialog();
	};

	const handleLocationSelectCookieMode: handleLocationAndTypeSelectFn =
		useCallback(
			(mcsLocation, mcsLocationConstType) => {
				if (
					cookieLocationType != mcsLocationConstType ||
					cookieLocationId != mcsLocation.id
				) {
					setLocationTypeCookie(
						COOKIE_LOCATION_TYPE,
						mcsLocationConstType
					);
					setLocationIdCookie(COOKIE_LOCATION_ID, mcsLocation.id);
					location.reload();
				}
			},
			[
				cookieLocationId,
				cookieLocationType,
				setLocationIdCookie,
				setLocationTypeCookie,
			]
		);

	const handleLocationSelectSubdomainMode: handleLocationAndTypeSelectFn =
		useCallback(
			(mcsLocation, mcsLocationConstType) => {
				if (
					cookieLocationType != mcsLocationConstType ||
					cookieLocationId != mcsLocation.id
				) {
					setLocationTypeCookie(
						COOKIE_LOCATION_TYPE,
						mcsLocationConstType,
						{
							domain: `.${options.base_domain}`,
						}
					);
					setLocationIdCookie(COOKIE_LOCATION_ID, mcsLocation.id, {
						domain: `.${options.base_domain}`,
					});
				}

				if (
					data.current_location_id != mcsLocation.id ||
					data.current_location_type != mcsLocationConstType
				) {
					const url = new URL(window.location.href);
					let newHost;
					if (
						mcsLocationConstType == defaultLocationType &&
						mcsLocation.id == defaultLocation.id
					) {
						newHost = options.base_domain;
					} else {
						newHost = `${mcsLocation.subdomain}.${options.base_domain}`;
					}

					if (url.hostname != newHost) {
						url.hostname = newHost;
						location.replace(url.href);
					}
				}
			},
			[
				cookieLocationType,
				cookieLocationId,
				setLocationTypeCookie,
				options.base_domain,
				setLocationIdCookie,
				data.current_location_id,
				data.current_location_type,
				defaultLocationType,
				defaultLocation.id,
			]
		);

	const handleLocationSelectSubFolderMode: handleLocationAndTypeSelectFn =
		useCallback(
			(mcsLocation, mcsLocationConstType) => {
				// console.log("mcsLocation", mcsLocation);
				// console.log("mcsLocationConstType", mcsLocationConstType);
				if (
					cookieLocationType != mcsLocationConstType ||
					cookieLocationId != mcsLocation.id
				) {
					setLocationTypeCookie(
						COOKIE_LOCATION_TYPE,
						mcsLocationConstType,
						{
							domain: `.${options.base_domain}`,
						}
					);
					setLocationIdCookie(COOKIE_LOCATION_ID, mcsLocation.id, {
						domain: `.${options.base_domain}`,
					});
				}

				if (
					data.current_location_id != mcsLocation.id ||
					data.current_location_type != mcsLocationConstType
				) {
					const url = new URL(window.location.href);
					if (
						data.current_location_id == defaultLocation.id &&
						data.current_location_type == defaultLocationType
					) {
						// console.log("replace default");
						url.pathname =
							`/${mcsLocation.subdomain}` + url.pathname;
					} else {
						// console.log("replace custom");
						// console.log(url.pathname);
						if (
							mcsLocation.id == defaultLocation.id &&
							mcsLocationConstType == defaultLocationType
						) {
							url.pathname = url.pathname.replace(/\/[^/?]+/, "");
						} else {
							url.pathname = url.pathname.replace(
								/\/[^/?]+/,
								`/${mcsLocation.subdomain}`
							);
						}
					}
					// console.log(url.pathname);
					location.replace(url.href);
				}
			},
			[
				cookieLocationType,
				cookieLocationId,
				setLocationTypeCookie,
				options.base_domain,
				setLocationIdCookie,
				data.current_location_id,
				data.current_location_type,
				defaultLocationType,
				defaultLocation.id,
			]
		);

	const handleLocationSelect: handleLocationAndTypeSelectFn = useCallback(
		(mcsLocation, mcsLocationType) => {
			switch (options.seo_mode) {
				case SEO_MODE_COOKIE:
					handleLocationSelectCookieMode(
						mcsLocation,
						mcsLocationType
					);
					break;
				case SEO_MODE_SUBDOMAIN:
					handleLocationSelectSubdomainMode(
						mcsLocation,
						mcsLocationType
					);
					break;
				case SEO_MODE_SUBFOLDER:
					handleLocationSelectSubFolderMode(
						mcsLocation,
						mcsLocationType
					);
					break;
			}

			if (mcsLocationType == LOCATION_TYPE_CITY) {
				setSelectedCityId(mcsLocation?.id);
			}
			setCookieDisablePopup(COOKIE_DISABLE_POPUP, "1", {
				domain: `.${options.base_domain}`,
			});

			setOpen(false);
			setShowPopup(false);
		},
		[
			handleLocationSelectCookieMode,
			handleLocationSelectSubFolderMode,
			handleLocationSelectSubdomainMode,
			options.base_domain,
			options.seo_mode,
			setCookieDisablePopup,
		]
	);

	const handleCitySelect: handleLocationSelectFn = useCallback(
		(mcsLocation) => {
			handleLocationSelect(mcsLocation, LOCATION_TYPE_CITY);
		},
		[handleLocationSelect]
	);

	const handleCountrySelect: handleLocationSelectFn = useCallback(
		(mcsLocation) => {
			handleLocationSelect(mcsLocation, LOCATION_TYPE_COUNTRY);
		},
		[handleLocationSelect]
	);

	const classes = useStyles();

	if (_.isEmpty(data.cities)) {
		return null;
	}

	const getCurrentLocation = () => {
		switch (data.current_location_type) {
			case LOCATION_TYPE_CITY:
				return data.cities[data.current_location_id];
			case LOCATION_TYPE_PROVINCE:
				return data.provinces[data.current_location_id];
			case LOCATION_TYPE_COUNTRY:
				return data.countries[data.current_location_id];
			default:
				return options.default_location_id
					? data.cities[options.default_location_id]
					: null;
		}
	};

	const currentLocation = getCurrentLocation();

	return (
		<ThemeProvider theme={McsTheme}>
			<Box m={2}>
				<Link
					id="mcs-link"
					aria-describedby="mcs-popup"
					href="#"
					onClick={handleLinkClick}
					//@ts-ignore
					ref={linkRef}
				>
					{currentLocation
						? `Location: ${currentLocation.title}`
						: "Choose location"}
				</Link>
				<McsPopup
					showPopup={showPopup}
					onClose={() => setShowPopup(false)}
					title={defaultLocation.title}
					handleLocationSelect={() =>
						handleLocationSelect(
							defaultLocation,
							defaultLocationType
						)
					}
					handleClose={handleShowDialog}
					//@ts-ignore
					anchorEl={() => linkRef.current}
				/>
			</Box>
			<Dialog
				id="mcs-dialog"
				open={open}
				onClose={handleClose}
				scroll="paper"
				aria-labelledby="scroll-dialog-title"
				classes={{
					paper: classes.paper,
				}}
				fullWidth
				maxWidth={
					[LIST_MODE_CITIES, LIST_MODE_COUNTRIES].includes(
						options.list_mode
					)
						? "xs"
						: "md"
				}
			>
				<DialogTitle onClose={handleClose}>
					{options.title ?? ""}
				</DialogTitle>
				<DialogContent className={classes.root}>
					{options?.list_mode === LIST_MODE_CITIES && (
						<ListCities
							selectedCityIndex={selectedCityId}
							data={data}
							onSelectCity={handleCitySelect}
						/>
					)}
					{options?.list_mode ===
						LIST_MODE_COUNTRIES_PROVINCES_CITIES && (
						<ListCountriesProvincesCities
							data={data}
							onSelectCity={handleCitySelect}
						/>
					)}
					{options?.list_mode === LIST_MODE_PROVINCES_CITIES && (
						<ListProvincesCities
							data={data}
							onSelectCity={handleCitySelect}
						/>
					)}
					{options?.list_mode === LIST_MODE_COUNTRIES_CITIES && (
						<ListCountriesCities
							data={data}
							onSelectCity={handleCitySelect}
						/>
					)}
					{options?.list_mode === LIST_MODE_COUNTRIES && (
						<ListCountries
							data={data}
							onSelectCountry={handleCountrySelect}
						/>
					)}
				</DialogContent>
			</Dialog>
		</ThemeProvider>
	);
};
