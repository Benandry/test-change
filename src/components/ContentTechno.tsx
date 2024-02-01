import React, { FC } from "react";
import StackTechno from "./StackTechno";
import { IContentTech } from "../interface/ITech";

const ContentTechno: FC<IContentTech> = ({ image, title, stack }) => {
  return (
    <div
      className={`max-w-xl xl:max-w-xl  p-3 bg-primary border border-gray-200 rounded-lg shadow hover:bg-blue-900 dark:bg-blue-800 dark:border-blue-700 dark:hover:bg-blue-700`}
    >
      <div className="text-semibold text-center">
        <h1 className="text-2xl font-semibold text-white">{title}</h1>
      </div>
      <div className="image_ flex justify-center p-3">
        <img
          className="object-cover w-full rounded-t-lg h-96 md:h-auto md:w-48 md:rounded-none md:rounded-l-lg"
          src={image}
          alt=""
        />
      </div>
      <div className="flex justify-center items-center">
        <div className="p-4 leading-normal" style={{ width: "100%" }}>
          <div className="mt-4">
            {stack.map(({ icon, title, progress }, index) => (
              <StackTechno
                key={index}
                title={title}
                progress={progress}
                icon={icon}
              />
            ))}
          </div>
        </div>
      </div>
    </div>
  );
};

export default ContentTechno;
