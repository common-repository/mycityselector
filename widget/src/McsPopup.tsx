import { Button, ButtonGroup, Popover, Typography } from "@material-ui/core";
import React from "react";
import { makeStyles } from "@material-ui/core/styles";
import { McsTheme } from "./McsWidget";

const useStyles = makeStyles(() => ({
	popup: {
		padding: McsTheme.spacing(1),
		textAlign: "center",
	},
}));

interface McsPopupProps {
	showPopup: boolean;
	onClose: () => void;
	title: string;
	handleLocationSelect: () => void;
	handleClose: () => void;
	anchorEl: null | Element | ((element: Element) => Element);
}

export const McsPopup: React.FC<McsPopupProps> = ({
	showPopup,
	onClose,
	title,
	handleLocationSelect,
	handleClose,
	anchorEl,
}: McsPopupProps) => {
	const classes = useStyles();
	return (
		<Popover
			id="mcs-popup"
			open={showPopup}
			anchorEl={anchorEl}
			onClose={onClose}
			anchorOrigin={{
				vertical: "bottom",
				horizontal: "center",
			}}
			transformOrigin={{
				vertical: "top",
				horizontal: "center",
			}}
			classes={{
				paper: classes.popup,
			}}
		>
			<Typography>Is {title} your city?</Typography>
			<ButtonGroup
				variant="contained"
				size="small"
				aria-label="contained primary button group"
			>
				<Button
					id="mcs-popup-yes"
					color="primary"
					onClick={handleLocationSelect}
				>
					Yes
				</Button>
				<Button
					id="mcs-popup-no"
					color="secondary"
					onClick={handleClose}
				>
					No
				</Button>
			</ButtonGroup>
		</Popover>
	);
};
