/*
 * This file is part of Composer Update Analyser package.
 *
 * @author Jean-Baptiste Nahan <814683+macintoshplus@users.noreply.github.com>
 * @copyright 2016-2019 - Jean-Baptiste Nahan
 * @license MIT
 */

--- FOR MSSQL

SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

if not exists (select * from sysobjects where name='dependencies' and xtype='U')
CREATE TABLE [dbo].[dependencies](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[project] [nvarchar](50) NOT NULL,
	[library] [nvarchar](250) NOT NULL,
	[version] [nvarchar](250) NOT NULL,
	[state] [nvarchar](20) NOT NULL,
	[to_library] [nvarchar](250) DEFAULT NULL,
	[to_version] [nvarchar](250) DEFAULT NULL,
	[deprecated] [bit] DEFAULT NULL,
	[updated_at] [datetime2](7) NOT NULL,
 CONSTRAINT [PK_dependencies] PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO

if not exists (select * from sysobjects where name='idx_proj_lib' and xtype='UQ')
ALTER TABLE [dbo].[dependencies] ADD  CONSTRAINT [idx_proj_lib] UNIQUE NONCLUSTERED
(
	[project] ASC,
	[library] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO

if not exists (select * from sysobjects where name='security' and xtype='U')
CREATE TABLE [dbo].[security](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[project] [nvarchar](50) NOT NULL,
	[library] [nvarchar](255) NOT NULL,
	[version] [nvarchar](255) NOT NULL,
	[state] [nvarchar](20) NOT NULL,
	[details] [text] NOT NULL,
	[updated_at] [datetime2](7) NOT NULL,
 CONSTRAINT [PK_security] PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO


if not exists (select * from sysobjects where name='projects' and xtype='U')
CREATE TABLE [dbo].[projects] (
  [code] [nvarchar](10) NOT NULL,
  [name] [nvarchar](100) NOT NULL,
  [path] [nvarchar](255) NOT NULL,
  [lock_path] [nvarchar](255) NOT NULL,
  [php_path] [nvarchar](255) NOT NULL,
  [private_dependencies] [nvarchar](max) DEFAULT NULL,
  [private_dependencies_strategy] [nvarchar](10) DEFAULT 'remove',
  [check_dependencies] [bit] DEFAULT NULL,
  [check_security] [bit] DEFAULT NULL,
  [updated_at] [datetime2](7) NOT NULL
 CONSTRAINT [PK_project] PRIMARY KEY CLUSTERED
(
	[code] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO

