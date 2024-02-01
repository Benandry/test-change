import React, { FC } from "react";
import { ICard } from "../interface/ICard";
import { Link } from "react-router-dom";

const Card: FC<ICard> = ({ title, paragraph, logo }) => {
  return (
    <div
      className={` grid md:grid-cols-2 sm:grid-cols-1 gap-4 sm:max-w-sm  md:max-w-lg xl:max-w-xl height_ p-6 bg-primary border border-gray-200 rounded-lg shadow hover:bg-blue-900 dark:bg-blue-800 dark:border-blue-700 dark:hover:bg-blue-700`}
    >
      <div className="logo-skill">
        <img src={logo} className="rounded-t-lg " alt="" />
      </div>
      <div className="info-skill">
        <Link to="#">
          <h5 className="mb-2 md:text-md lg:text-lg xl:text-2xl  font-semibold tracking-tight text-gray-200 ">
            {title}
          </h5>
        </Link>
        <p className="mb-3 sm:text-[18px] lg:text-sm md:text-lg  lg:leading-[1.5]   font-normal text-gray-300">
          {paragraph}
        </p>
        <Link
          to="#"
          className="inline-flex items-center text-blue-400 hover:underline"
        >
          See our guideline
          <svg
            className="w-3 h-3 ml-2.5"
            aria-hidden="true"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 18 18"
          >
            <path
              stroke="currentColor"
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeWidth="2"
              d="M15 11v4.833A1.166 1.166 0 0 1 13.833 17H2.167A1.167 1.167 0 0 1 1 15.833V4.167A1.166 1.166 0 0 1 2.167 3h4.618m4.447-2H17v5.768M9.111 8.889l7.778-7.778"
            />
          </svg>
        </Link>
      </div>
    </div>
  );
};

export default Card;
