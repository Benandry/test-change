import React, { FC } from "react";
import { IStackTechno } from "../interface/ITech";
const StackTechno: FC<IStackTechno> = ({ title, progress, icon }) => {
  return (
    <div className="stack flex items-center justify-center gap-4 mb-5">
      <div className="icon_stack text-white " style={{ flexBasis: "10%" }}>
        <img
          className="object-cover w-full rounded-t-lg ms:h-12 md:h-auto md:w-48 md:rounded-none md:rounded-l-lg"
          src={icon}
          alt=""
        />
      </div>
      <div className="progress_stack" style={{ flexBasis: "90%" }}>
        <div className="mb-1 text-base font-medium text-gray-100 dark:text-gray-500 uppercase">
          {title}
        </div>
        <div className="w-full bg-gray-200 rounded-full h-2.5 mb-4 dark:bg-gray-700">
          <div
            className="bg-blue-600 h-2.5 rounded-full"
            style={{ width: `${progress}%` }}
          ></div>
        </div>
      </div>
    </div>
  );
};

export default StackTechno;
