import React from "react";
import ReactDOM from "react-dom";
import { McsWidget } from "./McsWidget";
import { LIST_MODE_CITIES } from "./types/constants";

ReactDOM.render(
	<React.StrictMode>
		<McsWidget options={window.mcs?.options} data={window.mcs?.data} />
	</React.StrictMode>,
	document.getElementById("mcs-widget")
);
